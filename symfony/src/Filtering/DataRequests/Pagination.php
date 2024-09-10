<?php

namespace App\Filtering\DataRequests;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Nette\StaticClass;
use Psl\Math;
use Psl\Dict;
use Psl\Vec;

final class Pagination
{
    use StaticClass;

    public const PAGE_SIZE = 50;

    public static function countPages(Paginator $paginator, int $pageSize): int
    {
        return (int) Math\max([1, Math\ceil($paginator->count() / $pageSize)]);
    }

    public static function getFirst(int $pageSize, int $pageNumber): int
    {
        return $pageSize * ($pageNumber - 1);
    }

    /**
     * @return list<int>
     */
    public static function getPaginationPages(int $pageNumber, int $pagesCount): array
    {
        $candidates = [
            1,
            $pageNumber - 2,
            $pageNumber - 1,
            $pageNumber,
            $pageNumber + 1,
            $pageNumber + 2,
            $pagesCount,
        ];

        $result = Vec\filter(
            $candidates,
            fn (int $candidate): bool => $candidate >= 1 && $candidate <= $pagesCount,
        );

        return Vec\sort(Vec\values(Dict\unique($result)));
    }
}
