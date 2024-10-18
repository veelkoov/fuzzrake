<?php

namespace App\Filtering\DataRequests;

use App\Utils\Artisan\SmartAccessDecorator as Creator;

final readonly class CreatorsPage
{
    /**
     * @param list<Creator> $creators
     * @param list<int>     $paginationPages
     */
    public function __construct(
        public array $creators,
        public int $totalNumber,
        public int $pageNumber,
        public int $pagesCount,
        public array $paginationPages,
    ) {
    }

    public function hasPrev(): bool
    {
        return $this->pageNumber > 1;
    }

    public function hasNext(): bool
    {
        return $this->pageNumber < $this->pagesCount;
    }
}
