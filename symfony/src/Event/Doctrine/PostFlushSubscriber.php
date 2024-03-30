<?php

declare(strict_types=1);

namespace App\Event\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Psr\Cache\CacheItemPoolInterface;

#[AsDoctrineListener(event: Events::postFlush)]
class PostFlushSubscriber
{
    public function __construct(
        private readonly CacheItemPoolInterface $cacheProvider,
    ) {
    }

    public function postFlush(): void
    {
        $this->cacheProvider->clear();
    }
}
