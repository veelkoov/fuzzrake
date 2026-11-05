<?php

declare(strict_types=1);

namespace App\Filtering\RequestsHandling;

use App\Repository\CreatorRepository;
use App\Service\Cache;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Pagination\ItemsPage;
use App\ValueObject\CacheTags;

class FilteredDataProvider
{
    public function __construct(
        private readonly CreatorRepository $repository,
        private readonly Cache $cache,
    ) {
    }

    /**
     * @return ItemsPage<Creator>
     */
    public function getCreatorsPage(Choices $choices): ItemsPage
    {
        return $this->cache->get(
            fn () => $this->filterCreatorsBy($choices),
            CacheTags::CREATORS,
            [__METHOD__, $choices->getCacheDigest()],
        );
    }

    /**
     * @return ItemsPage<Creator>
     */
    private function filterCreatorsBy(Choices $choices): ItemsPage
    {
        $appender = new QueryChoicesAppender($choices);
        $page = $this->repository->getFilteredPage($appender, $choices->pageNumber);

        $creators = iter_mapl($page,
            // grep-code-cannot-use-coalesce-in-doctrine-order-by
            static fn (array $creatorAndOrderColumns) => new Creator($creatorAndOrderColumns[0]),
        );

        return new ItemsPage(
            $creators,
            $page->getTotalCount(),
            $page->getPageNumber(),
            $page->getPageCount(),
        );
    }
}
