<?php

declare(strict_types=1);

namespace App\Filters;

use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Closure;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function Psl\Vec\map;
use function Psl\Vec\values;

class CachedFiltered
{
    public function __construct(
        private readonly ArtisanRepository $repository,
        private readonly TagAwareCacheInterface $cache,
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

        return $this->cache->get($cacheKey, $this->getClosure($choices));
    }

    private function getClosure(Choices $choices): Closure
    {
        return function () use ($choices) {
            $artisans = Artisan::wrapAll($this->repository->getFiltered($choices));

            return map($artisans, fn (Artisan $artisan) => values($artisan->getPublicData()));
        };
    }
}
