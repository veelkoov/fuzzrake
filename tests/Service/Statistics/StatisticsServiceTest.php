<?php

declare(strict_types=1);

namespace App\Tests\Service\Statistics;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Repository\ArtisanVolatileDataRepository;
use App\Service\Statistics\StatisticsService;
use App\Tests\TestUtils\DbEnabledKernelTestCase;

class StatisticsServiceTest extends DbEnabledKernelTestCase
{
    public function testUnknownAndEuArtisansDontCountTowardsTotalCountries(): void
    {
        $a1 = (new Artisan())->setCountry(''); // Unknown should not count
        $a2 = (new Artisan())->setCountry('EU'); // European Union generic should not count grep-country-eu
        $a3 = (new Artisan())->setCountry('FI'); // Normal - should count

        self::bootKernel();
        self::persistAndFlush($a1, $a2, $a3);

        $artisanRepository = self::getEM()->getRepository(Artisan::class);
        $avdRepositoryMock = self::createMock(ArtisanVolatileDataRepository::class);

        self::assertInstanceOf(ArtisanRepository::class, $artisanRepository);
        $subject = new StatisticsService($artisanRepository, $avdRepositoryMock);
        $result = $subject->getMainPageStats();

        self::assertEquals(1, $result->getCountryCount());
    }

    public function testInactiveArtisansDontCountTowardsTotalActive(): void
    {
        $a1 = (new Artisan())->setInactiveReason(''); // Active should be counted
        $a2 = (new Artisan())->setInactiveReason(''); // Active should be counted
        $a3 = (new Artisan())->setInactiveReason('This is an inactive maker'); // Inactive should not be counted

        self::bootKernel();
        self::persistAndFlush($a1, $a2, $a3);

        $artisanRepository = self::getEM()->getRepository(Artisan::class);
        $avdRepositoryMock = self::createMock(ArtisanVolatileDataRepository::class);

        self::assertInstanceOf(ArtisanRepository::class, $artisanRepository);
        $subject = new StatisticsService($artisanRepository, $avdRepositoryMock);
        $result = $subject->getMainPageStats();

        self::assertEquals(2, $result->getActiveArtisansCount());
    }
}
