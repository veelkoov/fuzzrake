<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Entity\Artisan as CreatorE;
use App\Repository\ArtisanRepository;
use App\Service\Cache;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\ValueObject\CacheTags;
use Psl\Vec;

class FilteredDataProvider
{
    public function __construct(
        private readonly ArtisanRepository $repository,
        private readonly Cache $cache,
    ) {
    }

    public function getCreatorsPage(Choices $choices): CreatorsPage
    {
        return $this->cache->get(
            fn () => $this->filterCreatorsBy($choices),
            CacheTags::ARTISANS,
            [__METHOD__, $choices->getCacheDigest()],
        );
    }

    private function filterCreatorsBy(Choices $choices): CreatorsPage
    {
        // TODO: Where is the page number filtered?

        $appender = new QueryChoicesAppender($choices);
        $paginator = $this->repository->getFiltered($appender);
        $creators = Vec\map($paginator, fn (CreatorE $creator) => Creator::wrap($creator));

        $pagesCount = Pagination::countPages($paginator, $choices->pageSize);

        return new CreatorsPage(
            $creators,
            $paginator->count(),
            $choices->pageNumber,
            $pagesCount,
            Pagination::getPaginationPages($choices->pageNumber, $pagesCount),
        );
    }
}
