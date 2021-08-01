<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\Data\ArtisanChanges;
use App\Utils\StringList;
use App\Utils\Traits\UtilityClass;

class EventFactory
{
    use UtilityClass;

    public static function forCsTracker(ArtisanChanges $changes): Event
    {
        $original = $changes->getSubject();
        $changed = $changes->getChanged();

        $noLongerOpenFor = array_diff($original->getOpenForArray(), $changed->getOpenForArray());
        $nowOpenFor = array_diff($changed->getOpenForArray(), $original->getOpenForArray());

        return (new Event())
            ->setType(Event::TYPE_CS_UPDATED)
            ->setCheckedUrls($changed->getCommissionsUrl())
            ->setArtisanName($changed->getName())
            ->setTrackingIssues($changed->getCsTrackerIssue())
            ->setNoLongerOpenFor(StringList::pack($noLongerOpenFor))
            ->setNowOpenFor(StringList::pack($nowOpenFor));
    }
}
