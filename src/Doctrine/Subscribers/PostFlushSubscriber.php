<?php

declare(strict_types=1);

namespace App\Doctrine\Subscribers;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;

class PostFlushSubscriber implements EventSubscriber
{
    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    public function __construct(CacheProvider $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postFlush,
        ];
    }

    public function postFlush()
    {
        $this->cacheProvider->flushAll();
    }
}
