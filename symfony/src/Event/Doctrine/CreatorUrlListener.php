<?php

declare(strict_types=1);

namespace App\Event\Doctrine;

use App\Entity\CreatorUrl;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class CreatorUrlListener
{
    public function preUpdate(CreatorUrl $url, PreUpdateEventArgs $event): void
    {
        if ($event->getNewValue('url') !== $event->getOldValue('url')) {
            $url->resetFetchResults();
        }
    }
}
