<?php

declare(strict_types=1);

namespace App\Event\Doctrine;

use App\Entity\Artisan;
use App\Utils\Artisan\SmartAccessDecorator;
use Doctrine\ORM\Event\PreFlushEventArgs;

class ArtisanListener
{
    /** @noinspection PhpUnusedParameterInspection */
    public function preFlush(Artisan $artisan, PreFlushEventArgs $event): void
    {
        SmartAccessDecorator::wrap($artisan)->assureNsfwSafety();
    }
}
