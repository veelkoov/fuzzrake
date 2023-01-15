<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class Cache
{
    public function __construct(
        private readonly TagAwareCacheInterface $cache,
    ) {
    }

    /**
     * @template T
     *
     * @param list<string>|string $tags
     * @param callable(): T       $callback
     *
     * @return T
     */
    public function getCached(string $key, array|string $tags, callable $callback): mixed
    {
        try {
            return $this->cache->get($key, function (ItemInterface $item) use ($tags, $callback) {
                $item->tag($tags);

                return $callback();
            });
        } catch (InvalidArgumentException|CacheException $exception) {
            throw new RuntimeException(previous: $exception);
        }
    }

    /**
     * @param list<string>|string $tags
     */
    public function invalidate(array|string $tags): void
    {
        try {
            $this->cache->invalidateTags(is_string($tags) ? [$tags] : $tags);
        } catch (InvalidArgumentException $exception) {
            throw new RuntimeException(previous: $exception);
        }
    }
}
