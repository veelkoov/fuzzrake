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
use DateTimeInterface;
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
        $csTrackerIssue = false;
        $lastCsUpdate = null;

        $allOfferStatuses = $this->getAllOfferStatuses($artisan, $lastCsUpdate, $csTrackerIssue);
        $offerStatuses = $this->getResolvedOfferStatuses($allOfferStatuses, $artisan, $csTrackerIssue);

        return $this->getArtisanChangesGiven($artisan, $offerStatuses, $csTrackerIssue, $lastCsUpdate);
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

    /**
     * @return OfferStatus[]
     */
    private function getAllOfferStatuses(Artisan $artisan, ?DateTimeInterface &$lastCsUpdate, bool &$csTrackerIssue): array
    {
        $result = [];

        foreach ($artisan->getUrlObjs(Fields::URL_COMMISSIONS) as $url) {
            $lastCsUpdate = $url->getState()->getLastRequest();

            try {
                $allOfferStatuses = $this->extractOfferStatuses($url);

                if (0 === count($allOfferStatuses)) {
                    $this->logger->notice('No statuses detected in URL', [
                        'artisan' => (string) $artisan,
                        'url'     => (string) $url,
                    ]);

                    $csTrackerIssue = true;
                }

                array_push($result, ...$allOfferStatuses);
            } catch (ExceptionInterface | TrackerException $exception) {
                $this->logger->notice('Exception caught while detecting statuses in URL', [
                    'artisan'   => (string) $artisan,
                    'url'       => (string) $url,
                    'exception' => $exception,
                ]);

                $csTrackerIssue = true;
            }
        }

        return $result;
    }

    /**
     * @param OfferStatus[] $allOfferStatuses
     *
     * @return OfferStatus[]
     */
    private function getResolvedOfferStatuses(array $allOfferStatuses, Artisan $artisan, bool &$csTrackerIssue): array
    {
        $result = [];

        foreach ($allOfferStatuses as $offerStatus) {
            if (!array_key_exists($offerStatus->getOffer(), $result)) {
                // This is a status for an offer we didn't have previously
                $result[$offerStatus->getOffer()] = $offerStatus;
                continue;
            }

            // We have at best a duplicated offer

            $this->logger->notice('Duplicated status detected', [
                'artisan' => (string) $artisan,
                'offer'   => $offerStatus->getOffer(),
            ]);

            $csTrackerIssue = true;

            $previousStatus = $result[$offerStatus->getOffer()];

            if (null === $previousStatus) {
                // We have a 3rd+ offer with different statuses

                $this->logger->notice('Contradicting statuses detected (more than once)', [
                    'artisan' => (string) $artisan,
                    'offer'   => $offerStatus->getOffer(),
                ]);

                continue;
            }

            if ($previousStatus->getStatus() != $offerStatus->getStatus()) {
                // We have a 2nd offer and the status differs

                $this->logger->notice('Contradicting statuses detected', [
                    'artisan' => (string) $artisan,
                    'offer'   => $offerStatus->getOffer(),
                ]);

                $result[$offerStatus->getOffer()] = null;
            }
        }

        return array_filter($result, fn (?OfferStatus $item): bool => null !== $item);
    }

    private function getArtisanChangesGiven(Artisan $artisan, array $offerStatuses, bool $csTrackerIssue, $lastCsUpdate): ArtisanChanges
    {
        $result = new ArtisanChanges($artisan);
        $result->getChanged()->getVolatileData()->setCsTrackerIssue($csTrackerIssue);
        $result->getChanged()->getVolatileData()->setLastCsUpdate($lastCsUpdate);

        foreach ([true, false] as $status) {
            $offersMatchingStatus = array_filter($offerStatuses, fn (OfferStatus $item): bool => $item->getStatus() === $status);

            $newValue = StringList::pack(array_map(fn (OfferStatus $item): string => ucfirst(strtolower($item->getOffer())), $offersMatchingStatus));

            Commission::set($result->getChanged(), $status, $newValue);
        }

        return $result;
    }
}
