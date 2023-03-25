<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests;

use App\Filtering\DataRequests\Choices;
use App\Filtering\DataRequests\FilteredDataProvider;
use App\Filtering\DataRequests\Filters\SpeciesFilterFactory;
use App\Tests\TestUtils\CacheUtils;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
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

        $subject = new FilteredDataProvider(self::getArtisanRepository(), $this->getSpeciesFilterFactoryMock(), CacheUtils::getArrayBased());

        $result = $subject->getPublicDataFor(new Choices('', [], [], [], [], [], [], [], [], [], false, false, false, false, false, false));
        self::assertEquals('M000002', self::makerIdsFromPubData($result));

        $result = $subject->getPublicDataFor(new Choices('', [], [], [], [], [], [], [], [], [], false, false, false, false, true, false));
        self::assertEquals('M000002', self::makerIdsFromPubData($result));
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

        $subject = new FilteredDataProvider(self::getArtisanRepository(), $this->getSpeciesFilterFactoryMock(), CacheUtils::getArrayBased());

        $result = $subject->getPublicDataFor(new Choices('', [], [], [], [], [], [], [], [], [], false, false, false, true, true, false));
        self::assertEquals('M000001', self::makerIdsFromPubData($result));

        $result = $subject->getPublicDataFor(new Choices('', [], [], [], [], [], [], [], [], [], false, false, false, true, false, false));
        self::assertEquals('M000001, M000002, M000003, M000004, M000005, M000006, M000007', self::makerIdsFromPubData($result));
    }

    /**
     * @dataProvider wantsPaymentPlansDataProvider
     */
    public function testWantsPaymentPlans(bool $unknown, bool $none, bool $any, string $expected): void
    {
        self::bootKernel();

        $a1 = Artisan::new()->setMakerId('M000001');
        $a2 = Artisan::new()->setMakerId('M000002')->setPaymentPlans('None');
        $a3 = Artisan::new()->setMakerId('M000003')->setPaymentPlans('Some payment plan');

        self::persistAndFlush($a1, $a2, $a3);

        $subject = new FilteredDataProvider(self::getArtisanRepository(), $this->getSpeciesFilterFactoryMock(), CacheUtils::getArrayBased());

        $result = $subject->getPublicDataFor(new Choices('', [], [], [], [], [], [], [], [], [], $unknown, $any, $none, true, false, false));
        self::assertEquals($expected, self::makerIdsFromPubData($result));
    }

    /**
     * @return array<string, array{bool, bool, bool, string}>
     */
    public function wantsPaymentPlansDataProvider(): array
    {
        return [
            'Nothing selected'               => [false, false, false, 'M000001, M000002, M000003'],
            'Unknown selected'               => [true, false, false, 'M000001'],
            'Unknown and none selected'      => [true, true, false, 'M000001, M000002'],
            'Any selected'                   => [false, false, true, 'M000003'],
            'Any and none selected'          => [false, true, true, 'M000002, M000003'],
            'Any and unknown selected'       => [true, false, true, 'M000001, M000003'],
            'Any, none and unknown selected' => [true, true, true, 'M000001, M000002, M000003'],
        ];
    }

    /**
     * @param array<array<psJsonFieldValue>> $publicData
     */
    private static function makerIdsFromPubData(array $publicData): string
    {
        $makerIds = Vec\map($publicData, fn ($row) => $row[0]);

        return Str\join(Vec\sort($makerIds), ', ');
    }

    private function getSpeciesFilterFactoryMock(): SpeciesFilterFactory
    {
        return $this->createMock(SpeciesFilterFactory::class);
    }
}
