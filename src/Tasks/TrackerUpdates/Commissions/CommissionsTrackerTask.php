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
        $hasUrls = false;

        /* @var $newItems ArtisanCommissionsStatus[] */
        $newItems = [];

        foreach ($artisan->getUrlObjs(Fields::URL_COMMISSIONS) as $url) {
            $result->getChanged()->getVolatileData()->setLastCsUpdate($url->getState()->getLastRequest());
            $hasUrls = true;

            try {
                array_push($newItems, ...$this->extractArtisanCommissionsStatuses($url));
            } catch (ExceptionInterface | TrackerException) {
                $result->getChanged()->getVolatileData()->setCsTrackerIssue(true);
                // TODO: Log some information
            }
        }

        if ($hasUrls && 0 === count($newItems)) {
            $result->getChanged()->getVolatileData()->setCsTrackerIssue(true);
            // TODO: Log some information
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
            $result->getChanged()->getVolatileData()->setCsTrackerIssue(true);

            $previousStatus = $statuses[$status->getOffer()];

            if (null === $previousStatus) {
                // We have a 3rd+ offer with different statuses
                // TODO: Log some information
                continue;
            }

            if ($previousStatus->getIsOpen() != $status->getIsOpen()) {
                // We have a 2nd offer and the status differs
                // TODO: Log some information
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
