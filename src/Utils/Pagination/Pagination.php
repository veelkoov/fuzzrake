<?php

declare(strict_types=1);

namespace App\Utils\Pagination;

use App\Utils\Traits\UtilityClass;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\OffsetPaginator;
use Doctrine\ORM\Tools\Pagination\Window;
use Doctrine\ORM\Tools\Pagination\WindowPage;
use Veelkoov\Debris\Sets\IntSet;

final class Pagination
{
    use UtilityClass;

    public const int PAGE_SIZE = 25;

    /**
     * @template T
     *
     * @param Query<null, T> $query
     *
     * @return WindowPage<T>
     */
    public static function getExistingPage(Query $query, int $pageNumber): WindowPage
    {
        if ($pageNumber <= 0) {
            $pageNumber = 1;
        }

        while (true) {
            $page = new OffsetPaginator()->paginate($query, Pagination::getWindow($pageNumber));

            if ($pageNumber > $page->getPageCount()) {
                $pageNumber = $page->getPageCount();
            } else {
                return $page;
            }
        }
    }

    public static function getWindow(int $pageNumber): Window
    {
        return Window::fromPageNumberAndSize($pageNumber, self::PAGE_SIZE);
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
