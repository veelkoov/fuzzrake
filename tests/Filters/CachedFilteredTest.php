<?php

declare(strict_types=1);

namespace App\Tests\Filters;

use App\Filters\CachedFiltered;
use App\Filters\Choices;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Closure;
use Psl\Str;
use Psl\Vec;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

const F = false;
const T = true;

class CachedFilteredTest extends KernelTestCaseWithEM
{
    /**
     * @throws InvalidArgumentException
     */
    public function testWorkingWithMinors(): void
    {
        self::bootKernel();

        $a1 = Artisan::new()->setMakerId('M000001')->setWorksWithMinors(F);
        $a2 = Artisan::new()->setMakerId('M000002')->setWorksWithMinors(T);
        $a3 = Artisan::new()->setMakerId('M000003');

        foreach ([$a1, $a2, $a3] as $a) {
            $a->setNsfwSocial(F)->setNsfwWebsite(F)->setDoesNsfw(F);
        }

        self::persistAndFlush($a1, $a2, $a3);

        $subject = new CachedFiltered(self::getArtisanRepository(), $this->getCacheMock());

        $result = $subject->getPublicDataFor(new Choices([], [], [], [], [], [], [], [], [], F, F, F, F, F));
        self::assertEquals('M000002', self::makerIdsFromPubData($result));

        $result = $subject->getPublicDataFor(new Choices([], [], [], [], [], [], [], [], [], F, F, F, F, T));
        self::assertEquals('M000002', self::makerIdsFromPubData($result));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testWantsSfw(): void
    {
        self::bootKernel();

        $a1 = Artisan::new()->setMakerId('M000001')->setNsfwWebsite(F)->setNsfwSocial(F);
        $a2 = Artisan::new()->setMakerId('M000002')->setNsfwWebsite(T)->setNsfwSocial(F);
        $a3 = Artisan::new()->setMakerId('M000003')->setNsfwWebsite(F)->setNsfwSocial(T);
        $a4 = Artisan::new()->setMakerId('M000004')->setNsfwWebsite(T)->setNsfwSocial(T);
        $a5 = Artisan::new()->setMakerId('M000005')->setNsfwWebsite(T);
        $a6 = Artisan::new()->setMakerId('M000006')->setNsfwSocial(T);
        $a7 = Artisan::new()->setMakerId('M000007');

        self::persistAndFlush($a1, $a2, $a3, $a4, $a5, $a6, $a7);

        $subject = new CachedFiltered(self::getArtisanRepository(), $this->getCacheMock());

        $result = $subject->getPublicDataFor(new Choices([], [], [], [], [], [], [], [], [], F, F, F, T, T));
        self::assertEquals('M000001', self::makerIdsFromPubData($result));

        $result = $subject->getPublicDataFor(new Choices([], [], [], [], [], [], [], [], [], F, F, F, T, F));
        self::assertEquals('M000001, M000002, M000003, M000004, M000005, M000006, M000007', self::makerIdsFromPubData($result));
    }

    /**
     * @dataProvider wantsPaymentPlansDataProvider
     *
     * @throws InvalidArgumentException
     */
    public function testWantsPaymentPlans(bool $unknown, bool $none, bool $any, string $expected): void
    {
        self::bootKernel();

        $a1 = Artisan::new()->setMakerId('M000001');
        $a2 = Artisan::new()->setMakerId('M000002')->setPaymentPlans('None');
        $a3 = Artisan::new()->setMakerId('M000003')->setPaymentPlans('Some payment plan');

        self::persistAndFlush($a1, $a2, $a3);

        $subject = new CachedFiltered(self::getArtisanRepository(), $this->getCacheMock());

        $result = $subject->getPublicDataFor(new Choices([], [], [], [], [], [], [], [], [], $unknown, $any, $none, T, F));
        self::assertEquals($expected, self::makerIdsFromPubData($result));
    }

    /**
     * @return array<string, array{bool, bool, bool, string}>
     */
    public function wantsPaymentPlansDataProvider(): array
    {
        return [
            'Nothing selected'               => [F, F, F, 'M000001, M000002, M000003'],
            'Unknown selected'               => [T, F, F, 'M000001'],
            'Unknown and none selected'      => [T, T, F, 'M000001, M000002'],
            'Any selected'                   => [F, F, T, 'M000003'],
            'Any and none selected'          => [F, T, T, 'M000002, M000003'],
            'Any and unknown selected'       => [T, F, T, 'M000001, M000003'],
            'Any, none and unknown selected' => [T, T, T, 'M000001, M000002, M000003'],
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

    private function getCacheMock(): TagAwareCacheInterface
    {
        $result = $this->createMock(TagAwareCacheInterface::class);

        $result->method('get')->willReturnCallback(fn (string $tag, Closure $closure): mixed => $closure());

        return $result;
    }
}
