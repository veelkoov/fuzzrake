<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Data\Definitions\Fields\Field;
use App\Entity\EventFactory;
use App\IuHandling\Changes\Description;
use App\Tracking\OfferStatus\OffersStatusesProcessor;
use App\Tracking\OfferStatus\OffersStatusesResult;
use App\Tracking\OfferStatus\OfferStatus;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class StatusTracker
{
    private function logArtisanUpdates(Description $updates, Artisan $artisan): void
    {
        foreach ($updates->getChanges() as $change) {
            $this->logger->info($change->getDescription(), ['artisan' => (string) $artisan]);

            if (Field::OPEN_FOR === $change->getField()) {
                $this->entityManager->persist(EventFactory::forStatusTracker($change, $artisan));
            }
        }
    }
}
