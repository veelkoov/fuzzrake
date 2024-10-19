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
        $pagesCount = null;

        do {
            $choices = $this->getChoicesWithFixedPageNumber($choices, $pagesCount);

            $appender = new QueryChoicesAppender($choices);
            $paginator = $this->repository->getFiltered($appender);

            $pagesCount = Pagination::countPages($paginator, $choices->pageSize);
        } while ($choices->pageNumber > $pagesCount);

        $creators = Vec\map($paginator, fn (CreatorE $creator) => Creator::wrap($creator));

        return new CreatorsPage(
            $creators,
            $paginator->count(),
            $choices->pageNumber,
            $pagesCount,
            Pagination::getPaginationPages($choices->pageNumber, $pagesCount),
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
