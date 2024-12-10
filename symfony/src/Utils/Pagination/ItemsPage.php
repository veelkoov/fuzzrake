<?php

namespace App\Utils\Pagination;

/**
 * @template T
 */
final readonly class ItemsPage
{
    /**
     * @var list<int>
     */
    public array $paginationPages;

    public bool $hasPrev;
    public bool $hasNext;

    /**
     * @param list<T> $items
     */
    public function __construct(
        public array $items,
        public int $totalItems,
        public int $pageNumber,
        public int $totalPages,
    ) {
        $this->paginationPages = Pagination::getPaginationPages($pageNumber, $totalPages);
        $this->hasPrev = $this->pageNumber > 1;
        $this->hasNext = $this->pageNumber < $this->totalPages;
    }
}
