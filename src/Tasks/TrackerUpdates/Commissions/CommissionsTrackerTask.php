<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates\Commissions;

use App\Entity\Artisan;
use App\Entity\ArtisanCommissionsStatus;
use App\Entity\ArtisanUrl;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\TrackerTaskInterface;
use App\Utils\Artisan\Fields;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Tracking\CommissionsStatusParser;
use App\Utils\Tracking\OfferStatus;
use App\Utils\Tracking\TrackerException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class CommissionsTrackerTask implements TrackerTaskInterface
{
    /**
     * @var Artisan[]
     */
    private array $artisans;

    public function __construct(
        private ArtisanRepository $repository,
        private LoggerInterface $logger,
        private WebpageSnapshotManager $snapshots,
        private CommissionsStatusParser $parser,
    ) {
        $this->artisans = $this->repository->findAll();
    }

    /**
     * @return ArtisanUrl[]
     */
    public function getUrlsToPrefetch(): array
    {
        return array_merge(...array_map(fn (Artisan $artisan): array => $artisan->getUrlObjs(Fields::URL_COMMISSIONS), $this->artisans));
    }

    /**
     * @return ArtisanChanges[]
     */
    public function getUpdates(): array
    {
        return array_map(fn (Artisan $artisan) => $this->getUpdatesFor($artisan), $this->artisans);
    }

    private function getUpdatesFor(Artisan $artisan): ArtisanChanges
    {
        $result = new ArtisanChanges($artisan);
        $result->getChanged()->getCommissions()->clear();
        $result->getChanged()->getVolatileData()->setCsTrackerIssue(false);
        $result->getChanged()->getVolatileData()->setLastCsUpdate(null);

        /* @var $newItems ArtisanCommissionsStatus[] */
        $newItems = [];

        foreach ($artisan->getUrlObjs(Fields::URL_COMMISSIONS) as $url) {
            $result->getChanged()->getVolatileData()->setLastCsUpdate($url->getState()->getLastRequest());

            try {
                $statuses = $this->extractArtisanCommissionsStatuses($url);

                if (0 === count($statuses)) {
                    $this->logger->notice('No statuses detected in URL', [
                        'artisan' => (string) $artisan,
                        'url'     => (string) $url,
                    ]);

                    $result->getChanged()->getVolatileData()->setCsTrackerIssue(true);
                }

                array_push($newItems, ...$statuses);
            } catch (ExceptionInterface | TrackerException $exception) {
                $this->logger->notice('Exception caught while detecting statuses in URL', [
                    'artisan'   => (string) $artisan,
                    'url'       => (string) $url,
                    'exception' => $exception,
                ]);

                $result->getChanged()->getVolatileData()->setCsTrackerIssue(true);
            }
        }

        /* @var $statuses ArtisanCommissionsStatus[] */
        $statuses = [];

        foreach ($newItems as $status) {
            if (!array_key_exists($status->getOffer(), $statuses)) {
                // This is a status for an offer we didn't have previously
                $result->getChanged()->getCommissions()->add($status);
                $statuses[$status->getOffer()] = $status;
                continue;
            }

            // We have at best a duplicated offer

            $this->logger->notice('Duplicated status detected', [
                'artisan' => (string) $artisan,
                'offer'   => $status->getOffer(),
            ]);

            $result->getChanged()->getVolatileData()->setCsTrackerIssue(true);

            $previousStatus = $statuses[$status->getOffer()];

            if (null === $previousStatus) {
                // We have a 3rd+ offer with different statuses

                $this->logger->notice('Contradicting statuses detected (more than once)', [
                    'artisan' => (string) $artisan,
                    'offer'   => $status->getOffer(),
                ]);

                continue;
            }

            if ($previousStatus->getIsOpen() != $status->getIsOpen()) {
                // We have a 2nd offer and the status differs

                $this->logger->notice('Contradicting statuses detected', [
                    'artisan' => (string) $artisan,
                    'offer'   => $status->getOffer(),
                ]);

                $result->getChanged()->getCommissions()->removeElement($previousStatus);
                $statuses[$status->getOffer()] = null;
            }
        }

        return $result;
    }

    /**
     * @return ArtisanCommissionsStatus[]
     *
     * @throws TrackerException
     * @throws ExceptionInterface
     */
    private function extractArtisanCommissionsStatuses(ArtisanUrl $url): array
    {
        $webpageSnapshot = $this->snapshots->get($url, false, false);

        return array_map(function (OfferStatus $match) use ($url): ArtisanCommissionsStatus {
            return (new ArtisanCommissionsStatus())
                ->setIsOpen($match->getStatus())
                ->setOffer(ucfirst(strtolower($match->getOffer())))
                ->setArtisan($url->getArtisan());
        }, $this->parser->getCommissionsStatuses($webpageSnapshot));
    }
}
