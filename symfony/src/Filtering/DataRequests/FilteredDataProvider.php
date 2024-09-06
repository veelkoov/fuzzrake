<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Repository\ArtisanRepository;
use App\Service\Cache;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\ValueObject\CacheTags;

class FilteredDataProvider
{
    public function __construct(
        private readonly ArtisanRepository $repository,
        private readonly Cache $cache,
    ) {
    }

    /**
     * @return list<Creator>
     */
    public function getFilteredCreators(Choices $choices): array
    {
        return $this->cache->getCached('Filtered.creatorsObjects.'.$choices->getCacheDigest(),
            CacheTags::ARTISANS, fn () => $this->filterCreatorsBy($choices));
    }

    /**
     * @return list<Creator>
     */
    private function filterCreatorsBy(Choices $choices): array
    {
        $appender = new QueryChoicesAppender($choices);

        return Creator::wrapAll($this->repository->getFiltered($appender));
    }
}
