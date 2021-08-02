<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates\Commissions;

use App\DataDefinitions\Fields;
use App\Entity\Artisan;
use App\Entity\ArtisanUrl;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\TrackerTaskInterface;
use App\Utils\Accessors\Commission;
use App\Utils\Data\ArtisanChanges;
use App\Utils\StringList;
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
        $result->getChanged()->getVolatileData()->setCsTrackerIssue(false);
        $result->getChanged()->getVolatileData()->setLastCsUpdate(null);

        /* @var $newItems OfferStatus[] */
        $newItems = [];

        foreach ($artisan->getUrlObjs(Fields::URL_COMMISSIONS) as $url) {
            $result->getChanged()->getVolatileData()->setLastCsUpdate($url->getState()->getLastRequest());

            try {
                $allOfferStatuses = $this->extractOfferStatuses($url);

                if (0 === count($allOfferStatuses)) {
                    $this->logger->notice('No statuses detected in URL', [
                        'artisan' => (string) $artisan,
                        'url'     => (string) $url,
                    ]);

                    $result->getChanged()->getVolatileData()->setCsTrackerIssue(true);
                }

                array_push($newItems, ...$allOfferStatuses);
            } catch (ExceptionInterface | TrackerException $exception) {
                $this->logger->notice('Exception caught while detecting statuses in URL', [
                    'artisan'   => (string) $artisan,
                    'url'       => (string) $url,
                    'exception' => $exception,
                ]);

                $result->getChanged()->getVolatileData()->setCsTrackerIssue(true);
            }
        }

        /* @var $offerStatuses OfferStatus[] */
        $offerStatuses = [];

        foreach ($newItems as $status) {
            if (!array_key_exists($status->getOffer(), $offerStatuses)) {
                // This is a status for an offer we didn't have previously
                $offerStatuses[$status->getOffer()] = $status;
                continue;
            }

            // We have at best a duplicated offer

            $this->logger->notice('Duplicated status detected', [
                'artisan' => (string) $artisan,
                'offer'   => $status->getOffer(),
            ]);

            $result->getChanged()->getVolatileData()->setCsTrackerIssue(true);

            $previousStatus = $offerStatuses[$status->getOffer()];

            if (null === $previousStatus) {
                // We have a 3rd+ offer with different statuses

                $this->logger->notice('Contradicting statuses detected (more than once)', [
                    'artisan' => (string) $artisan,
                    'offer'   => $status->getOffer(),
                ]);

                continue;
            }

            if ($previousStatus->getStatus() != $status->getStatus()) {
                // We have a 2nd offer and the status differs

                $this->logger->notice('Contradicting statuses detected', [
                    'artisan' => (string) $artisan,
                    'offer'   => $status->getOffer(),
                ]);

                $offerStatuses[$status->getOffer()] = null;
            }
        }

        foreach ([true, false] as $status) {
            Commission::set($result->getChanged(), $status, StringList::pack(array_map(fn (string $item): string => ucfirst(strtolower($item)), array_keys(array_filter($offerStatuses, fn (?OfferStatus $item): bool => $item?->getStatus() === $status)))));
        }

        return $result;
    }

    /**
     * @return OfferStatus[]
     *
     * @throws TrackerException
     * @throws ExceptionInterface
     */
    private function extractOfferStatuses(ArtisanUrl $url): array
    {
        $webpageSnapshot = $this->snapshots->get($url, false, false);

        return $this->parser->getCommissionsStatuses($webpageSnapshot);
    }
}
