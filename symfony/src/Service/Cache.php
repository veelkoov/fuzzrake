<?php

declare(strict_types=1);

namespace App\Service;

use App\Data\Definitions\Fields\Field;
use Psl\Vec;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @phpstan-type Keyable Field|string
 */
class Cache
{
    public function __construct(
        private readonly TagAwareCacheInterface $cache,
    ) {
    }

    /**
     * @deprecated Use get() instead
     *
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
     * @template T
     *
     * @param callable(): T       $callback
     * @param list<string>|string $tags
     * @param Keyable[]|Keyable   $keyParts
     *
     * @return T
     */
    public function get(callable $callback, array|string $tags, mixed $keyParts): mixed
    {
        if (!is_array($keyParts)) {
            $keyParts = [$keyParts];
        }

        $keyParts = Vec\map((array) $keyParts, function (mixed $item): string {
            if ($item instanceof Field) {
                $result = $item->value;
            } elseif (is_string($item)) {
                $result = $item;
            } else {
                throw new RuntimeException(gettype($item).' is not supported as a cache key part');
            }

            return str_replace('.', '..', $result);
        });

        $key = implode('.', $keyParts);

        return $this->getCached($key, $tags, $callback);
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
