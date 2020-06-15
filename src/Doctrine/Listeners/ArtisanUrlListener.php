<?php

declare(strict_types=1);

namespace App\Doctrine\Listeners;

use App\Entity\ArtisanUrl;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class ArtisanUrlListener
{
    public function preUpdate(ArtisanUrl $url, PreUpdateEventArgs $event): void
    {
        if ($event->getNewValue('url') !== $event->getOldValue('url')) {
            $url->setLastFailure(null);
            $url->setLastSuccess(null);
            $url->setLastFailureReason('');
            $url->setLastFailureCode(0);
        }
    }
}
