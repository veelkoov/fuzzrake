<?php

declare(strict_types=1);

namespace App\Tests\Service\Statistics;

use App\Repository\ArtisanVolatileDataRepository;
use App\Repository\CreatorOfferStatusRepository;
use App\Repository\KotlinDataRepository;
use App\Service\DataService;
use App\Tests\TestUtils\CacheUtils;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

/**
 * @medium
 */
class StatisticsServiceWithEMTest extends KernelTestCaseWithEM
{
    public function testUnknownAndEuArtisansDontCountTowardsTotalCountries(): void
    {
        $a1 = (new Artisan())->setCountry(''); // Unknown should not count
        $a2 = (new Artisan())->setCountry('EU'); // European Union generic should not count grep-country-eu
        $a3 = (new Artisan())->setCountry('FI'); // Normal - should count

        self::bootKernel();
        self::persistAndFlush($a1, $a2, $a3);

        $artisanRepository = self::getArtisanRepository();
        $avdRepositoryMock = self::createMock(ArtisanVolatileDataRepository::class);
        $acsRepositoryMock = self::createMock(CreatorOfferStatusRepository::class);
        $kdRepositoryMock = self::createMock(KotlinDataRepository::class);

        $subject = new DataService($artisanRepository, $avdRepositoryMock,
            $acsRepositoryMock, $kdRepositoryMock, CacheUtils::getArrayBased());
        $result = $subject->getMainPageStats();

        self::assertEquals(1, $result->countryCount);
    }

    public function testInactiveArtisansDontCountTowardsTotalActive(): void
    {
        $a1 = (new Artisan())->setInactiveReason(''); // Active should be counted
        $a2 = (new Artisan())->setInactiveReason(''); // Active should be counted
        $a3 = (new Artisan())->setInactiveReason('This is a hidden maker'); // Hidden should not be counted

        self::bootKernel();
        self::persistAndFlush($a1, $a2, $a3);

        $artisanRepository = self::getArtisanRepository();
        $avdRepositoryMock = self::createMock(ArtisanVolatileDataRepository::class);
        $acsRepositoryMock = self::createMock(CreatorOfferStatusRepository::class);
        $kdRepositoryMock = self::createMock(KotlinDataRepository::class);

        $subject = new DataService($artisanRepository, $avdRepositoryMock,
            $acsRepositoryMock, $kdRepositoryMock, CacheUtils::getArrayBased());
        $result = $subject->getMainPageStats();

        self::assertEquals(2, $result->activeArtisansCount);
    }
}
