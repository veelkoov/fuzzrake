<?php

declare(strict_types=1);

namespace App\Utils\Pagination;

use App\Utils\Traits\UtilityClass;
use Countable;
use Psl\Dict;
use Psl\Math;
use Psl\Vec;

final class Pagination
{
    use UtilityClass;

    public const int PAGE_SIZE = 25;

    public static function countPages(Countable $paginator, int $pageSize): int
    {
        return (int) Math\max([1, Math\ceil($paginator->count() / $pageSize)]);
    }

    public static function getFirstIdx(int $pageSize, int $pageNumber): int
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
