<?php

declare(strict_types=1);

namespace App\Entity;

use App\IuHandling\Changes\ChangeInterface;
use App\IuHandling\Changes\ListChange;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;
use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;

class EventFactory // TODO
{
    use UtilityClass;

    public static function forStatusTracker(ChangeInterface $changes, Artisan $updatedArtisan): Event
    {
        if (!($changes instanceof ListChange)) {
            throw new InvalidArgumentException('Unable to generate the event from a non-list change');
        }

        return (new Event())
            ->setType(Event::TYPE_CS_UPDATED)
            ->setCheckedUrls($updatedArtisan->getCommissionsUrls())
            ->setArtisanName($updatedArtisan->getName())
            ->setTrackingIssues($updatedArtisan->getCsTrackerIssue())
            ->setNoLongerOpenFor(StringList::pack($changes->removed))
            ->setNowOpenFor(StringList::pack($changes->added));
    }
}
