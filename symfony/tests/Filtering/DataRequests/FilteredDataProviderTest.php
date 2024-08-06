<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests;

use App\Filtering\DataRequests\Choices;
use App\Filtering\DataRequests\FilteredDataProvider;
use App\Tests\TestUtils\CacheUtils;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use Psl\Str;
use Psl\Vec;
use Psr\Cache\InvalidArgumentException;

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

        $result = $subject->getFilteredCreators(new Choices('', [], [], [], [], [], [], [], [], [], false, false, false, false, false, false));
        self::assertEquals('M000002', self::creatorsListToMakerIdList($result));

        $result = $subject->getFilteredCreators(new Choices('', [], [], [], [], [], [], [], [], [], false, false, false, false, true, false));
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

        $result = $subject->getFilteredCreators(new Choices('', [], [], [], [], [], [], [], [], [], false, false, false, true, true, false));
        self::assertEquals('M000001', self::creatorsListToMakerIdList($result));

        $result = $subject->getFilteredCreators(new Choices('', [], [], [], [], [], [], [], [], [], false, false, false, true, false, false));
        self::assertEquals('M000001, M000002, M000003, M000004, M000005, M000006, M000007', self::creatorsListToMakerIdList($result));
    }

    /**
     * @param list<Creator> $creators
     */
    private static function creatorsListToMakerIdList(array $creators): string
    {
        $makerIds = Vec\map($creators, fn (Creator $creator) => $creator->getMakerId());

        return Str\join(Vec\sort($makerIds), ', ');
    }
}
