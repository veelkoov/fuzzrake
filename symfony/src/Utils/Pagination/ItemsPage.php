<?php

declare(strict_types=1);

namespace App\Utils\Pagination;

use Veelkoov\Debris\IntSet;

/**
 * @template T
 */
final readonly class ItemsPage
{
    public IntSet $paginationPages;
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
