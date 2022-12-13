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

class CachedFilteredTest extends KernelTestCaseWithEM
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

        $subject = new CachedFiltered(self::getArtisanRepository(), $this->getCacheMock());

        $result = $subject->getPublicDataFor(new Choices([], [], [], [], [], [], [], [], [], [], false, false));
        self::assertEquals('M000002', self::makerIdsFromPubData($result));

        $result = $subject->getPublicDataFor(new Choices([], [], [], [], [], [], [], [], [], [], false, true));
        self::assertEquals('M000002', self::makerIdsFromPubData($result));
    }

    /**
     * @throws InvalidArgumentException
     */
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

        $subject = new CachedFiltered(self::getArtisanRepository(), $this->getCacheMock());

        $result = $subject->getPublicDataFor(new Choices([], [], [], [], [], [], [], [], [], [], true, true));
        self::assertEquals('M000001', self::makerIdsFromPubData($result));

        $result = $subject->getPublicDataFor(new Choices([], [], [], [], [], [], [], [], [], [], true, false));
        self::assertEquals('M000001, M000002, M000003, M000004, M000005, M000006, M000007', self::makerIdsFromPubData($result));
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
