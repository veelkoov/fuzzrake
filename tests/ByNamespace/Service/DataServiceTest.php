<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Service;

use App\Repository\CreatorOfferStatusRepository;
use App\Repository\CreatorValueRepository;
use App\Repository\CreatorVolatileDataRepository;
use App\Repository\EventRepository;
use App\Service\DataService;
use App\Tests\TestUtils\CacheUtils;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Tests\TestUtils\UserCreator;
use PHPUnit\Framework\Attributes\Medium;
use Psr\Log\LoggerInterface;

#[Medium]
class DataServiceTest extends FuzzrakeKernelTestCase
{
    public function testUnknownAndEuCreatorsDontCountTowardsTotalCountries(): void
    {
        $a1 = UserCreator::get()->setCountry(''); // Unknown should not count
        $a2 = UserCreator::get()->setCountry('EU'); // European Union generic should not count grep-country-eu
        $a3 = UserCreator::get()->setCountry('FI'); // Normal - should count

        self::persistAndFlush($a1, $a2, $a3);

        $subject = $this->getDataService();
        $result = $subject->getMainPageStats();

        self::assertSame(1, $result->countryCount);
    }

    public function testInactiveCreatorsDontCountTowardsTotalActive(): void
    {
        $a1 = UserCreator::get()->setInactiveReason(''); // Active should be counted
        $a2 = UserCreator::get()->setInactiveReason(''); // Active should be counted
        $a3 = UserCreator::get()->setInactiveReason('This is a hidden creator'); // Hidden should not be counted

        self::persistAndFlush($a1, $a2, $a3);

        $subject = $this->getDataService();
        $result = $subject->getMainPageStats();

        self::assertSame(2, $result->activeCreatorsCount);
    }

    public function testGetCreatorsPublicDataJsonString(): void
    {
        $creator1 = UserCreator::get()->setName('Active')->setInactiveReason('');
        $creator2 = UserCreator::get()->setName('Hidden')->setInactiveReason('This is a hidden creator');
        self::persistAndFlush($creator1, $creator2);

        $subject = $this->getDataService();

        $result1 = $subject->getCreatorsPublicDataJsonString(false);
        self::assertJson($result1);
        self::assertStringContainsString('Active', $result1);
        self::assertStringNotContainsString('Hidden', $result1);

        $result2 = $subject->getCreatorsPublicDataJsonString(true);
        self::assertJson($result2);
        self::assertStringContainsString('Active', $result2);
        self::assertStringContainsString('Hidden', $result2);
    }

    private function getDataService(): DataService
    {
        return new DataService(
            self::getCreatorRepository(),
            self::createStub(CreatorValueRepository::class),
            self::createStub(CreatorVolatileDataRepository::class),
            self::createStub(CreatorOfferStatusRepository::class),
            self::createStub(EventRepository::class),
            self::getEM(),
            CacheUtils::getArrayBased(),
            self::createStub(LoggerInterface::class),
        );
    }
}
