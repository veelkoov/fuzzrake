<?php

declare(strict_types=1);

namespace App\Utils\Pagination;

use App\Utils\Traits\UtilityClass;
use Countable;
use Veelkoov\Debris\IntSet;

final class Pagination
{
    use UtilityClass;

    public const int PAGE_SIZE = 25;

    public static function countPages(Countable $paginator, int $pageSize): int
    {
        return (int) max(1, ceil($paginator->count() / $pageSize));
    }

    public static function getFirstIdx(int $pageSize, int $pageNumber): int
    {
        return $pageSize * ($pageNumber - 1);
    }

    public static function getPaginationPages(int $pageNumber, int $pagesCount): IntSet
    {
        return new IntSet([
            1,
            $pageNumber - 2,
            $pageNumber - 1,
            $pageNumber,
            $pageNumber + 1,
            $pageNumber + 2,
            $pagesCount,
        ])
            ->filter(static fn (int $candidate): bool => $candidate >= 1 && $candidate <= $pagesCount)
            ->sorted()
            ->freeze();
    }
}
