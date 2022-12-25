<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider;

use App\Filtering\Choices;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

class CachedFiltered implements FilteredInterface
{
    public function __construct(
        private readonly Filtered $provider,
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @return array<array<string, psJsonFieldValue>>
     *
     * @throws InvalidArgumentException
     */
    public function getPublicDataFor(Choices $choices): array
    {
        $cacheKey = 'restapi.artisans-array.'.$choices->getDigest();

        return $this->cache->get($cacheKey, fn () => $this->provider->getPublicDataFor($choices));
    }
}
