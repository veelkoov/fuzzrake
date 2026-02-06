<?php

declare(strict_types=1);

namespace App\Tests\Service\Statistics;

use App\Repository\CreatorOfferStatusRepository;
use App\Repository\CreatorValueRepository;
use App\Repository\CreatorVolatileDataRepository;
use App\Repository\EventRepository;
use App\Service\DataService;
use App\Tests\TestUtils\CacheUtils;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\Medium;
use Psr\Log\LoggerInterface;

#[Medium]
class DataServiceTest extends FuzzrakeKernelTestCase
{
    public function testUnknownAndEuCreatorsDontCountTowardsTotalCountries(): void
    {
        $a1 = new Creator()->setCountry(''); // Unknown should not count
        $a2 = new Creator()->setCountry('EU'); // European Union generic should not count grep-country-eu
        $a3 = new Creator()->setCountry('FI'); // Normal - should count

        self::persistAndFlush($a1, $a2, $a3);

        $subject = $this->getDataService();
        $result = $subject->getMainPageStats();

        self::assertSame(1, $result->countryCount);
    }

    public function testInactiveCreatorsDontCountTowardsTotalActive(): void
    {
        $a1 = new Creator()->setInactiveReason(''); // Active should be counted
        $a2 = new Creator()->setInactiveReason(''); // Active should be counted
        $a3 = new Creator()->setInactiveReason('This is a hidden creator'); // Hidden should not be counted

        self::persistAndFlush($a1, $a2, $a3);

        $subject = $this->getDataService();
        $result = $subject->getMainPageStats();

        self::assertSame(2, $result->activeCreatorsCount);
    }

    private function getDataService(): DataService
    {
        return new DataService(
            self::getCreatorRepository(),
            self::createStub(CreatorValueRepository::class),
            self::createStub(CreatorVolatileDataRepository::class),
            self::createStub(CreatorOfferStatusRepository::class),
            self::createStub(EventRepository::class),
            CacheUtils::getArrayBased(),
            self::createStub(LoggerInterface::class),
        );
    }
}
