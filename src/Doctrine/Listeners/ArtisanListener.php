<?php

declare(strict_types=1);

namespace App\Doctrine\Listeners;

use App\Entity\Artisan;
use App\Utils\Artisan\SmartAccessDecorator;
use Doctrine\ORM\Event\PreFlushEventArgs;

class ArtisanListener
{
    public function preFlush(Artisan $artisan, PreFlushEventArgs $event): void
    {
        SmartAccessDecorator::wrap($artisan)->assureNsfwSafety();
    }
}
