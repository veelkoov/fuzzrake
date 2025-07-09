<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests;

use App\Filtering\DataRequests\Choices;
use App\Filtering\DataRequests\FilteredDataProvider;
use App\Tests\TestUtils\CacheUtils;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Pagination\ItemsPage;
use App\Utils\Parse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;
use Psr\Cache\InvalidArgumentException;
use TRegx\PhpUnit\DataProviders\DataProvider as TestDataProvider;
use Veelkoov\Debris\StringSet;

#[Medium]
class FilteredDataProviderTest extends FuzzrakeKernelTestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testWorkingWithMinors(): void
    {
        $a1 = new Creator()->setCreatorId('M000001')->setWorksWithMinors(false);
        $a2 = new Creator()->setCreatorId('M000002')->setWorksWithMinors(true);
        $a3 = new Creator()->setCreatorId('M000003');

        foreach ([$a1, $a2, $a3] as $a) {
            $a->setNsfwSocial(false)->setNsfwWebsite(false)->setDoesNsfw(false);
        }

        self::persistAndFlush($a1, $a2, $a3);

        $subject = new FilteredDataProvider(self::getCreatorRepository(), CacheUtils::getArrayBased());

        $result = $subject->getCreatorsPage(new Choices('', '', new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), false, false, false, false, false, false, false, 1));
        self::assertSame('M000002', self::creatorsListToCreatorIdList($result));

        $result = $subject->getCreatorsPage(new Choices('', '', new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), false, false, false, false, true, false, false, 1));
        self::assertSame('M000002', self::creatorsListToCreatorIdList($result));
    }

    public function testWantsSfw(): void
    {
        $a1 = new Creator()->setCreatorId('M000001')->setNsfwWebsite(false)->setNsfwSocial(false);
        $a2 = new Creator()->setCreatorId('M000002')->setNsfwWebsite(true)->setNsfwSocial(false);
        $a3 = new Creator()->setCreatorId('M000003')->setNsfwWebsite(false)->setNsfwSocial(true);
        $a4 = new Creator()->setCreatorId('M000004')->setNsfwWebsite(true)->setNsfwSocial(true);
        $a5 = new Creator()->setCreatorId('M000005')->setNsfwWebsite(true);
        $a6 = new Creator()->setCreatorId('M000006')->setNsfwSocial(true);
        $a7 = new Creator()->setCreatorId('M000007');

        self::persistAndFlush($a1, $a2, $a3, $a4, $a5, $a6, $a7);

        $subject = new FilteredDataProvider(self::getCreatorRepository(), CacheUtils::getArrayBased());

        $result = $subject->getCreatorsPage(new Choices('', '', new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), false, false, false, true, true, false, false, 1));
        self::assertSame('M000001', self::creatorsListToCreatorIdList($result));

        $result = $subject->getCreatorsPage(new Choices('', '', new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), false, false, false, true, false, false, false, 1));
        self::assertSame('M000001, M000002, M000003, M000004, M000005, M000006, M000007', self::creatorsListToCreatorIdList($result));
    }

    public static function paginatedResultsDataProvider(): TestDataProvider
    {
        // Page size is 25

        return TestDataProvider::tuples(
            [0,  1, 1, 1,  0,  0],
            [1,  1, 1, 1,  1,  1],
            [25, 1, 1, 1,  1, 25],
            [26, 1, 1, 2,  1, 25],
            [50, 2, 2, 2, 26, 50],
            [51, 2, 2, 3, 26, 50],
            [51, 3, 3, 3, 51, 51],

            [0, -1, 1, 1,  0,  0],
            [0,  2, 1, 1,  0,  0],
            [26, 3, 2, 2, 26, 26],
            [26, 5, 2, 2, 26, 26],
        );
    }

    #[DataProvider('paginatedResultsDataProvider')]
    public function testPaginatedResults(int $numberOfCreators, int $pageRequested, int $pageReturned, int $pagesCount,
        int $expectedFirst, int $expectedLast): void
    {
        for ($i = 1; $i <= $numberOfCreators; ++$i) {
            self::persist(new Creator()
                ->setName(sprintf('%03d', $i)) // For sorting
                ->setCity("$i") // For easy number access
            );
        }

        self::flush();

        $subject = new FilteredDataProvider(self::getCreatorRepository(), CacheUtils::getArrayBased());

        $input = new Choices('', '', new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), new StringSet(), true, true, true, true, false, true, false, $pageRequested);

        $result = $subject->getCreatorsPage($input);

        self::assertSame($numberOfCreators, $result->totalItems); // Sanity check
        self::assertSame($pageReturned, $result->pageNumber);
        self::assertSame($pagesCount, $result->totalPages);

        $first = Parse::int(Iter\first($result->items)?->getCity() ?? '0');
        $last = Parse::int(Iter\last($result->items)?->getCity() ?? '0');

        self::assertSame($expectedFirst, $first);
        self::assertSame($expectedLast, $last);
    }

    /**
     * @param ItemsPage<Creator> $pageData
     */
    private static function creatorsListToCreatorIdList(ItemsPage $pageData): string
    {
        $creatorIds = Vec\map($pageData->items, fn (Creator $creator) => $creator->getCreatorId());

        return Str\join(Vec\sort($creatorIds), ', ');
    }
}
