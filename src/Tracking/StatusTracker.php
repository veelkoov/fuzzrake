<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Data\Definitions\Fields\Field;
use App\Entity\ArtisanUrl;
use App\Entity\EventFactory;
use App\IuHandling\Changes\Description;
use App\Tracking\OfferStatus\OffersStatusesProcessor;
use App\Tracking\OfferStatus\OffersStatusesResult;
use App\Tracking\OfferStatus\OfferStatus;
use App\Tracking\Web\Url\CoercedUrl;
use App\Tracking\Web\Url\Fetchable;
use App\Tracking\Web\WebsiteInfo;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;
use function Psl\Vec\map;

class StatusTracker
{
    private readonly WebsiteInfo $websiteInfo;

    private function applyUpdatesFor(Artisan $artisan, OffersStatusesResult $offerStatuses): Description // TODO: Translate into plain logging
    {
        $before = clone $artisan;

        $artisan->setCsTrackerIssue($offerStatuses->csTrackerIssue);
        $artisan->setCsLastCheck($offerStatuses->lastCsUpdate);

        foreach ([true, false] as $status) {
            $offersMatchingStatus = array_filter($offerStatuses->offersStatuses, fn (OfferStatus $item): bool => $item->status === $status);

            $newValue = StringList::pack(map($offersMatchingStatus, fn (OfferStatus $item): string => ucfirst(strtolower($item->offer))));

            if ($status) {
                $artisan->setOpenFor($newValue);
            } else {
                $artisan->setClosedFor($newValue);
            }
        }

        return new Description($before, $artisan);
    }

    private function logArtisanUpdates(Description $updates, Artisan $artisan): void
    {
        foreach ($updates->getChanges() as $change) {
            $this->logger->info($change->getDescription(), ['artisan' => (string) $artisan]);

            if (Field::OPEN_FOR === $change->getField()) {
                $this->entityManager->persist(EventFactory::forStatusTracker($change, $artisan));
            }
        }
    }

    /**
     * @param ArtisanUrl[] $urls
     *
     * @return Fetchable[]
     */
    private function coerceUrlsToFetch(array $urls): array
    {
        return array_map(function (ArtisanUrl $url): Fetchable {
            $coercedUrl = $this->websiteInfo->coerceTrackingUrl($url->getUrl());

            if ($coercedUrl === $url->getUrl()) {
                return $url;
            } else {
                return new CoercedUrl($url, $coercedUrl);
            }
        }, $urls);
    }
}
