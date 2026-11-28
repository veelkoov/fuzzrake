<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\CacheKey;
use App\Utils\Exceptions\UncheckedException;
use App\ValueObject\Messages\InvalidateCacheTagsV1;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @phpstan-import-type Keyable from CacheKey
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
        $key = CacheKey::get($keyParts);

        try {
            $getCached = $this->cache->get($key, function (ItemInterface $item) use ($tags, $callback) {
                $item->tag($tags);

                return $callback();
            });
        } catch (InvalidArgumentException|CacheException $exception) {
            throw new UncheckedException($exception);
        }

        return $getCached;
    }

    #[AsMessageHandler]
    public function invalidate(InvalidateCacheTagsV1 $message): void
    {
        try {
            $this->cache->invalidateTags($message->tags->getValuesArray());
        } catch (InvalidArgumentException $exception) {
            throw new UncheckedException($exception);
        }
    }
}
