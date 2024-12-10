<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Entity\Artisan as CreatorE;
use App\Repository\ArtisanRepository;
use App\Service\Cache;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\Pagination\ItemsPage;
use App\Utils\Pagination\Pagination;
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
     * @return ItemsPage<Creator>
     */
    public function getCreatorsPage(Choices $choices): ItemsPage
    {
        return $this->cache->get(
            fn () => $this->filterCreatorsBy($choices),
            CacheTags::ARTISANS,
            [__METHOD__, $choices->getCacheDigest()],
        );
    }

    /**
     * @return ItemsPage<Creator>
     */
    private function filterCreatorsBy(Choices $choices): ItemsPage
    {
        $pagesCount = null;

        do {
            $choices = $this->getChoicesWithFixedPageNumber($choices, $pagesCount);

            $appender = new QueryChoicesAppender($choices);
            $paginator = $this->repository->getFiltered($appender);

            $pagesCount = Pagination::countPages($paginator, $choices->pageSize);
        } while ($choices->pageNumber > $pagesCount);

        $creators = Vec\map($paginator, fn (CreatorE $creator) => Creator::wrap($creator));

        return new ItemsPage(
            $creators,
            $paginator->count(),
            $choices->pageNumber,
            $pagesCount,
        );
    }

    private function getChoicesWithFixedPageNumber(Choices $choices, ?int $lastPageNumber): Choices
    {
        if ($choices->pageNumber < 1) {
            $newPageNumber = 1;
        } elseif (null !== $lastPageNumber && $choices->pageNumber > $lastPageNumber) {
            $newPageNumber = $lastPageNumber;
        } else {
            $newPageNumber = $choices->pageNumber;
        }

        if ($choices->pageNumber !== $newPageNumber) {
            $choices = $choices->changePage($newPageNumber);
        }

        return $choices;
    }
}
