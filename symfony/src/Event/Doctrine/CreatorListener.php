<?php

declare(strict_types=1);

namespace App\Event\Doctrine;

use App\Entity\Creator;
use App\Utils\Creator\SmartAccessDecorator;
use Doctrine\ORM\Event\PreFlushEventArgs;

class CreatorListener
{
    /** @noinspection PhpUnusedParameterInspection */
    public function preFlush(Creator $creator, PreFlushEventArgs $event): void
    {
        SmartAccessDecorator::wrap($creator)->assureNsfwSafety();
    }
}
