<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Utils\Pagination\Pagination;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;

#[Small]
class PaginationTest extends FuzzrakeTestCase
{
    /**
     * @param list<int> $expected
     */
    #[DataProvider('getPaginationPagesDataProvider')]
    public function testGetPaginationPages(int $totalPages, int $currentPage, array $expected): void
    {
        $result = Pagination::getPaginationPages($currentPage, $totalPages);

        self::assertEquals($expected, $result->getValuesArray());
    }

    /**
     * @return list<array{int, int, list<int>}>
     */
    public static function getPaginationPagesDataProvider(): array
    {
        // TOTAL PAGES, CURRENT PAGE, EXPECTED PAGINATOR PAGES
        // Supposed to return: current, +1, -1, +2, -2, first and last
        return [
            [0, 0, []],

            [1, 1, [1]],

            [2, 1, [1, 2]],
            [2, 2, [1, 2]],

            [3, 1, [1, 2, 3]],
            [3, 2, [1, 2, 3]],
            [3, 3, [1, 2, 3]],

            [4, 1, [1, 2, 3, 4]],
            [4, 2, [1, 2, 3, 4]],
            [4, 3, [1, 2, 3, 4]],
            [4, 4, [1, 2, 3, 4]],

            [5, 1, [1, 2, 3, 5]],
            [5, 2, [1, 2, 3, 4, 5]],
            [5, 3, [1, 2, 3, 4, 5]],
            [5, 4, [1, 2, 3, 4, 5]],
            [5, 5, [1, 3, 4, 5]],

            [6, 1, [1, 2, 3, 6]],
            [6, 2, [1, 2, 3, 4, 6]],
            [6, 3, [1, 2, 3, 4, 5, 6]],
            [6, 4, [1, 2, 3, 4, 5, 6]],
            [6, 5, [1, 3, 4, 5, 6]],
            [6, 6, [1, 4, 5, 6]],

            [7, 1, [1, 2, 3, 7]],
            [7, 2, [1, 2, 3, 4, 7]],
            [7, 3, [1, 2, 3, 4, 5, 7]],
            [7, 4, [1, 2, 3, 4, 5, 6, 7]],
            [7, 5, [1, 3, 4, 5, 6, 7]],
            [7, 6, [1, 4, 5, 6, 7]],
            [7, 7, [1, 5, 6, 7]],

            [8, 1, [1, 2, 3, 8]],
            [8, 2, [1, 2, 3, 4, 8]],
            [8, 3, [1, 2, 3, 4, 5, 8]],
            [8, 4, [1, 2, 3, 4, 5, 6, 8]],
            [8, 5, [1, 3, 4, 5, 6, 7, 8]],
            [8, 6, [1, 4, 5, 6, 7, 8]],
            [8, 7, [1, 5, 6, 7, 8]],
            [8, 8, [1, 6, 7, 8]],

            [9, 1, [1, 2, 3, 9]],
            [9, 2, [1, 2, 3, 4, 9]],
            [9, 3, [1, 2, 3, 4, 5, 9]],
            [9, 4, [1, 2, 3, 4, 5, 6, 9]],
            [9, 5, [1, 3, 4, 5, 6, 7, 9]],
            [9, 6, [1, 4, 5, 6, 7, 8, 9]],
            [9, 7, [1, 5, 6, 7, 8, 9]],
            [9, 8, [1, 6, 7, 8, 9]],
            [9, 9, [1, 7, 8, 9]],
        ];
    }
}
