<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests;

use App\Filtering\DataRequests\Choices;
use App\Filtering\DataRequests\FilteredDataProvider;
use App\Tests\TestUtils\CacheUtils;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\Pagination\ItemsPage;
use App\Utils\Parse;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;
use Psr\Cache\InvalidArgumentException;
use TRegx\PhpUnit\DataProviders\DataProvider;
use Veelkoov\Debris\StringList;

/**
 * @medium
 */
class FilteredDataProviderTest extends KernelTestCaseWithEM
{
    /**
     * @throws InvalidArgumentException
     */
    public function testWorkingWithMinors(): void
    {
        self::bootKernel();

        $a1 = Artisan::new()->setMakerId('M000001')->setWorksWithMinors(false);
        $a2 = Artisan::new()->setMakerId('M000002')->setWorksWithMinors(true);
        $a3 = Artisan::new()->setMakerId('M000003');

        foreach ([$a1, $a2, $a3] as $a) {
            $a->setNsfwSocial(false)->setNsfwWebsite(false)->setDoesNsfw(false);
        }

        self::persistAndFlush($a1, $a2, $a3);

        $subject = new FilteredDataProvider(self::getArtisanRepository(), CacheUtils::getArrayBased());

        $result = $subject->getCreatorsPage(new Choices('', '', new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), false, false, false, false, false, false, false, 1));
        self::assertEquals('M000002', self::creatorsListToMakerIdList($result));

        $result = $subject->getCreatorsPage(new Choices('', '', new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), false, false, false, false, true, false, false, 1));
        self::assertEquals('M000002', self::creatorsListToMakerIdList($result));
    }

    public function testWantsSfw(): void
    {
        self::bootKernel();

        $a1 = Artisan::new()->setMakerId('M000001')->setNsfwWebsite(false)->setNsfwSocial(false);
        $a2 = Artisan::new()->setMakerId('M000002')->setNsfwWebsite(true)->setNsfwSocial(false);
        $a3 = Artisan::new()->setMakerId('M000003')->setNsfwWebsite(false)->setNsfwSocial(true);
        $a4 = Artisan::new()->setMakerId('M000004')->setNsfwWebsite(true)->setNsfwSocial(true);
        $a5 = Artisan::new()->setMakerId('M000005')->setNsfwWebsite(true);
        $a6 = Artisan::new()->setMakerId('M000006')->setNsfwSocial(true);
        $a7 = Artisan::new()->setMakerId('M000007');

        self::persistAndFlush($a1, $a2, $a3, $a4, $a5, $a6, $a7);

        $subject = new FilteredDataProvider(self::getArtisanRepository(), CacheUtils::getArrayBased());

        $result = $subject->getCreatorsPage(new Choices('', '', new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), false, false, false, true, true, false, false, 1));
        self::assertEquals('M000001', self::creatorsListToMakerIdList($result));

        $result = $subject->getCreatorsPage(new Choices('', '', new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), false, false, false, true, false, false, false, 1));
        self::assertEquals('M000001, M000002, M000003, M000004, M000005, M000006, M000007', self::creatorsListToMakerIdList($result));
    }

    public function paginatedResultsDataProvider(): DataProvider
    {
        // Page size is 25

        return DataProvider::tuples(
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

    /**
     * @dataProvider paginatedResultsDataProvider
     */
    public function testPaginatedResults(int $numberOfCreators, int $pageRequested, int $pageReturned, int $pagesCount,
        int $expectedFirst, int $expectedLast): void
    {
        self::bootKernel();

        for ($i = 1; $i <= $numberOfCreators; ++$i) {
            self::persist(Creator::new()
                ->setName(sprintf('%03d', $i)) // For sorting
                ->setCity("$i") // For easy number access
            );
        }

        self::flush();

        $subject = new FilteredDataProvider(self::getArtisanRepository(), CacheUtils::getArrayBased());

        $input = new Choices('', '', new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), new StringList(), true, true, true, true, false, true, false, $pageRequested);

        $result = $subject->getCreatorsPage($input);

        self::assertEquals($numberOfCreators, $result->totalItems); // Sanity check
        self::assertEquals($pageReturned, $result->pageNumber);
        self::assertEquals($pagesCount, $result->totalPages);

        $first = Parse::int(Iter\first($result->items)?->getCity() ?? '0');
        $last = Parse::int(Iter\last($result->items)?->getCity() ?? '0');

        self::assertEquals($expectedFirst, $first);
        self::assertEquals($expectedLast, $last);
    }

    /**
     * @param ItemsPage<Creator> $pageData
     */
    private static function creatorsListToMakerIdList(ItemsPage $pageData): string
    {
        $makerIds = Vec\map($pageData->items, fn (Creator $creator) => $creator->getMakerId());

        return Str\join(Vec\sort($makerIds), ', ');
    }
}
