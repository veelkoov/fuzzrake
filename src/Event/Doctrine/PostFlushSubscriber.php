<?php

declare(strict_types=1);

namespace App\Event\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Psr\Cache\CacheItemPoolInterface;

class PostFlushSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly CacheItemPoolInterface $cacheProvider,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postFlush,
        ];
    }

    public function postFlush()
    {
        $this->cacheProvider->clear();
    }
}
