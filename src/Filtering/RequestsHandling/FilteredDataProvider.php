<?php

declare(strict_types=1);

namespace App\Filtering\RequestsHandling;

use App\Repository\CreatorRepository;
use App\Service\Cache;
use App\Service\DataService;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Pagination\ItemsPage;
use App\Utils\Pagination\Pagination;
use App\ValueObject\CacheTags;
use Doctrine\ORM\Query;

class FilteredDataProvider
{
    public function __construct(
        private readonly CreatorRepository $repository,
        private readonly DataService $dataService,
        private readonly Cache $cache,
    ) {
    }

    /**
     * @return ItemsPage<Creator>
     */
    public function getCreatorsPage(Choices $choices): ItemsPage
    {
        return $this->cache->get(
            fn () => $this->filterCreatorsBy($choices, $choices->pageNumber),
            CacheTags::CREATORS,
            [__METHOD__, $choices->getCacheDigest()],
        );
    }

    /**
     * @return ItemsPage<Creator>
     */
    private function filterCreatorsBy(Choices $choices, int $pageNumber): ItemsPage
    {
        $builder = $this->repository->createQueryBuilder('d_c')
            ->leftJoin('d_c.volatileData', 'd_cvd')->addSelect('d_cvd'); // One-to-one, nullable, hydrated always anyway
        $appender = new QueryChoicesAppender($choices);
        $appender->applyChoices($builder);

        $query = $builder->getQuery();
        /** @var Query<null, array{0: \App\Entity\Creator, 1: int}> $query */

        // @phpstan-ignore-next-line varTag.type
        $page = Pagination::getExistingPage($query, $pageNumber);

        $creators = iter_mapl($page,
            // grep-code-cannot-use-coalesce-in-doctrine-order-by
            fn (array $creatorAndOrderColumns) => $this->dataService->getCreatorDataById($creatorAndOrderColumns[0]->getId() ?? 0),
        );

        return new ItemsPage(
            $creators,
            $page->getTotalCount(),
            $page->getPageNumber(),
            $page->getPageCount(),
        );
    }
}
