<?php

declare(strict_types=1);

namespace App\Utils\Pagination;

use App\Utils\Traits\UtilityClass;
use Countable;
use Veelkoov\Debris\Sets\IntSet;

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

    public static function clamp(int $pageNumber, int $pagesCount): int
    {
        return max(1, min($pageNumber, $pagesCount));
    }

    public static function getPaginationPages(int $pageNumber, int $pagesCount): IntSet
    {
        if (0 === $pagesCount) {
            return new IntSet(frozen: true);
        }

        $result = new IntSet([1, $pageNumber, $pagesCount]);

        $expectedPagesNumber = min(7, $pagesCount);
        $mod = 1;
        while ($result->count() < $expectedPagesNumber) {
            $result->add(max(1, $pageNumber - $mod));
            $result->add(min($pagesCount, $pageNumber + $mod));

            ++$mod;
        }

        return $result->sorted()->freeze();
    }
}
