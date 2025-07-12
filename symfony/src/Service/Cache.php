<?php

declare(strict_types=1);

namespace App\Service;

use App\Data\Definitions\Fields\Field;
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
        $key = $this->getKeyFromParts($keyParts);

        try {
            $getCached = $this->cache->get($key, function (ItemInterface $item) use ($tags, $callback) {
                $item->tag($tags);

                return $callback();
            });
        } catch (InvalidArgumentException|CacheException $exception) {
            throw new RuntimeException(message: $exception->getMessage(), code: $exception->getCode(), previous: $exception);
        }

        return $getCached;
    }

    /**
     * @param list<string>|string $tags
     */
    public function invalidate(array|string $tags): void
    {
        try {
            $this->cache->invalidateTags(is_string($tags) ? [$tags] : $tags);
        } catch (InvalidArgumentException $exception) {
            throw new RuntimeException(message: $exception->getMessage(), code: $exception->getCode(), previous: $exception);
        }
    }

    /**
     * @param Keyable[]|Keyable $keyParts
     *
     * @internal
     */
    public function getKeyFromParts(mixed $keyParts): string
    {
        if (!is_array($keyParts)) {
            $keyParts = [$keyParts];
        }

        $keyParts = arr_map($keyParts, function (mixed $item): string {
            if ($item instanceof Field) {
                $result = $item->value;
            } elseif (is_string($item)) {
                $result = $item;
            } else {
                throw new RuntimeException(gettype($item).' is not supported as a cache key part');
            }

            return str_replace('.', '..', $result);
        });

        return implode('.', $keyParts);
    }
}
