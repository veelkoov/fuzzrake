<?php

declare(strict_types=1);

namespace App\Tests\Filtering\RequestsHandling;

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
     * @return iterable<array{int, int, list<int>}> TOTAL PAGES, CURRENT PAGE, EXPECTED PAGINATOR PAGES
     */
    public static function getPaginationPagesDataProvider(): iterable
    {
        yield [0, 0, []];

        // As long as we have no more than 7 pages, we return all of them
        for ($totalPages = 1; $totalPages <= 7; ++$totalPages) {
            for ($pageNumber = 1; $pageNumber <= $totalPages; ++$pageNumber) {
                yield [$totalPages, $pageNumber, range(1, $totalPages)];
            }
        }

        yield [8, 1, [1, 2, 3, 4, 5, 6,    8]];
        yield [8, 2, [1, 2, 3, 4, 5, 6,    8]];
        yield [8, 3, [1, 2, 3, 4, 5, 6,    8]];
        yield [8, 4, [1, 2, 3, 4, 5, 6,    8]];
        yield [8, 5, [1,    3, 4, 5, 6, 7, 8]];
        yield [8, 6, [1,    3, 4, 5, 6, 7, 8]];
        yield [8, 7, [1,    3, 4, 5, 6, 7, 8]];
        yield [8, 8, [1,    3, 4, 5, 6, 7, 8]];

        yield [9, 1, [1, 2, 3, 4, 5, 6,       9]];
        yield [9, 2, [1, 2, 3, 4, 5, 6,       9]];
        yield [9, 3, [1, 2, 3, 4, 5, 6,       9]];
        yield [9, 4, [1, 2, 3, 4, 5, 6,       9]];
        yield [9, 5, [1,    3, 4, 5, 6, 7,    9]];
        yield [9, 6, [1,       4, 5, 6, 7, 8, 9]];
        yield [9, 7, [1,       4, 5, 6, 7, 8, 9]];
        yield [9, 8, [1,       4, 5, 6, 7, 8, 9]];
        yield [9, 9, [1,       4, 5, 6, 7, 8, 9]];

        yield [10,  1, [1, 2, 3, 4, 5, 6,          10]];
        yield [10,  2, [1, 2, 3, 4, 5, 6,          10]];
        yield [10,  3, [1, 2, 3, 4, 5, 6,          10]];
        yield [10,  4, [1, 2, 3, 4, 5, 6,          10]];
        yield [10,  5, [1,    3, 4, 5, 6, 7,       10]];
        yield [10,  6, [1,       4, 5, 6, 7, 8,    10]];
        yield [10,  7, [1,          5, 6, 7, 8, 9, 10]];
        yield [10,  8, [1,          5, 6, 7, 8, 9, 10]];
        yield [10,  9, [1,          5, 6, 7, 8, 9, 10]];
        yield [10, 10, [1,          5, 6, 7, 8, 9, 10]];
    }
}
