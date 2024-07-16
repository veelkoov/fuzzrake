<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Repository\ArtisanRepository;
use App\Service\Cache;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\CacheTags;
use Psl\Vec;

class FilteredDataProvider
{
    public function __construct(
        private readonly ArtisanRepository $repository,
        private readonly Cache $cache,
    ) {
    }

    /**
     * @return array<array<psJsonFieldValue>>
     */
    public function getPublicDataFor(Choices $choices): array
    {
        return $this->cache->getCached('Filtered.artisans.'.$choices->getCacheDigest(),
            CacheTags::ARTISANS, fn () => $this->retrievePublicDataFor($choices));
    }

    /**
     * @return array<array<psJsonFieldValue>>
     */
    private function retrievePublicDataFor(Choices $choices): array
    {
        $appender = new QueryChoicesAppender($choices);

        $artisans = Artisan::wrapAll($this->repository->getFiltered($appender));

        return Vec\map($artisans, fn (Artisan $artisan) => Vec\values($artisan->getPublicData()));
    }
}
