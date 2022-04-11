<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\ArtisanVolatileDataRepository;
use App\Service\HealthCheckService;
use App\Utils\DateTime\UtcClock;
use App\Utils\DateTime\UtcClockForTests;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\TestCase;

class HealthCheckServiceTest extends TestCase
{
    private const HC_VALUES = [
        'MEMORY_AVAILABLE_MIN_MIBS'  => 256,
        'DISK_FREE_MIN_MIBS'         => 1024,
        'DISK_USED_MAX_PERCENT'      => 90,
        'LOAD_1M_MAX'                => 0.9,
        'LOAD_5M_MAX'                => 0.5,
        'LOAD_15M_MAX'               => 0.2,
    ];

    public static function setUpBeforeClass(): void
    {
        UtcClockForTests::reset();
    }

    public function testTimes(): void
    {
        $acsrMock = $this->createPartialMock(ArtisanVolatileDataRepository::class, ['getLastCsUpdateTime', 'getLastBpUpdateTime']);
        $csDateTime = DateTimeImmutable::createFromFormat('U', (string) (UtcClock::time() - 300), new DateTimeZone('UTC'));
        $bpDateTime = DateTimeImmutable::createFromFormat('U', (string) (UtcClock::time() - 900), new DateTimeZone('UTC'));
        $nowDateTime = DateTimeImmutable::createFromFormat('U', (string) UtcClock::time(), new DateTimeZone('UTC'));

        $acsrMock
            ->expects(self::exactly(2))
            ->method('getLastCsUpdateTime')
            ->willReturn($csDateTime);
        $acsrMock
            ->expects(self::exactly(2))
            ->method('getLastBpUpdateTime')
            ->willReturn($bpDateTime);

        $hcSrv = new HealthCheckService($acsrMock, self::HC_VALUES);
        $data = $hcSrv->getStatus();

        static::assertEquals($nowDateTime->format('Y-m-d H:i:s'), $data['serverTimeUtc']);
        static::assertEquals($csDateTime->format('Y-m-d H:i'), $data['lastCsUpdateUtc']);
        static::assertEquals($bpDateTime->format('Y-m-d H:i'), $data['lastBpUpdateUtc']);
    }

    /**
     * @dataProvider getXyzUpdatesStatusDataProvider
     *
     * @throws Exception
     */
    public function testGetXyzUpdatesStatus(string $repoMethodName, string $hcStatusResultKey, DateTimeImmutable $returnedDateTime, string $expectedResult): void
    {
        $avdrMock = $this->createMock(ArtisanVolatileDataRepository::class);
        $avdrMock
            ->expects(self::exactly(2))
            ->method($repoMethodName)
            ->willReturn($returnedDateTime)
        ;

        $hcSrv = new HealthCheckService($avdrMock, self::HC_VALUES);

        static::assertEquals($expectedResult, $hcSrv->getStatus()[$hcStatusResultKey]);
    }

    /**
     * @throws Exception
     */
    public function getXyzUpdatesStatusDataProvider(): array // grep-tracking-frequency
    {
        $utc = new DateTimeZone('UTC');

        return [
            ['getLastCsUpdateTime', 'csUpdatesStatus', new DateTimeImmutable('-12 hours -10 minutes', $utc), 'OK'],
            ['getLastCsUpdateTime', 'csUpdatesStatus', new DateTimeImmutable('-12 hours -20 minutes', $utc), 'WARNING'],
            ['getLastBpUpdateTime', 'bpUpdatesStatus', new DateTimeImmutable('-7 days -10 minutes', $utc), 'OK'],
            ['getLastBpUpdateTime', 'bpUpdatesStatus', new DateTimeImmutable('-7 days -20 minutes', $utc), 'WARNING'],
        ];
    }

    /**
     * @dataProvider getDiskStatusDataProvider
     */
    public function testGetDiskStatus(string $result, string $rawInput): void
    {
        static::assertEquals($result, $this->getHcService()->getDiskStatus($rawInput));
    }

    /**
     * @dataProvider getMemoryStatusDataProvider
     */
    public function testGetMemoryStatus(string $result, string $rawInput): void
    {
        static::assertEquals($result, $this->getHcService()->getMemoryStatus($rawInput));
    }

    /**
     * @dataProvider getLoadStatusDataProvider
     */
    public function testGetLoadStatus(string $result, string $cpuCountRawData, string $procLoadAvgRawData): void
    {
        static::assertEquals($result, $this->getHcService()->getLoadStatus($cpuCountRawData, $procLoadAvgRawData));
    }

    public function getDiskStatusDataProvider(): array
    {
        return [
            [
                'OK',
                <<<'END'

                26723	76%
                548	1%
                291059	84%
                
                END,
            ],
            [
                'OK',
                <<<'END'

                26723	76%
                548	1%
                291059	100%
                
                END,
            ],
            [
                'OK',
                <<<'END'

                3709	55%
                
                END,
            ],
            [
                'WARNING',
                <<<'END'

                26723	76%
                548	91%
                291059	84%
                
                END,
            ],
            [
                'WARNING',
                <<<'END'

                1009	91%
                
                END,
            ],
            [
                'WARNING',
                <<<'END'

                2009e	10%
                
                END,
            ],
            [
                'WARNING',
                <<<'END'

                2009	10%	e
                
                END,
            ],
            [
                'WARNING',
                <<<'END'

                26723	76%
                548	1%
                291059	100%
                some warning here
                
                END,
            ],
            [
                'WARNING',
                <<<'END'

                26723	76%
                548	1%

                291059	100%
                
                END,
            ],
            [ // Percent sign, but no percent value
                'WARNING',
                <<<'END'

                26723	76%
                548	%
                291059	100%
                
                END,
            ],
            [ // Tab, but no percent value
                'WARNING',
                <<<'END'

                26723	76%
                548	
                291059	100%
                
                END,
            ],
            ['WARNING', "\n"],
            ['WARNING', 'some error'],
        ];
    }

    public function getMemoryStatusDataProvider(): array
    {
        return [
            [
                'OK',
                <<<'END'

                8927
                
                END,
            ],
            [
                'WARNING',
                <<<'END'

                254
                
                END,
            ],
            ['WARNING', "\n"],
            ['WARNING', '512 e'],
        ];
    }

    public function getLoadStatusDataProvider(): array
    {
        return [
            ['OK', ' 1 ', " 0.1\t0.1\t0.1 "],
            ['WARNING', ' 1 ', " 0.1\t0.1\t0.2 "],
            ['OK', ' 2 ', " 0.1\t0.1\t0.2 "],
            ['WARNING', ' 1 ', " 0.1\t0.5\t0.1 "],
            ['OK', ' 2 ', " 0.1\t0.5\t0.1 "],
            ['WARNING', ' 1 ', " 0.9\t0.1\t0.1 "],
            ['OK', ' 2 ', " 0.9\t0.1\t0.1 "],
            ['WARNING', ' e ', " 0.1\t0.1\t0.1 "],
            ['WARNING', '  ', " 0.1\t0.1\t0.1 "],
            ['WARNING', ' 1 ', " 0.1\t0.1\t0.1\t0.1 "],
            ['WARNING', ' 1 ', ' '],
            ['WARNING', ' 1 ', " \t\t "],
            ['WARNING', ' 1 ', " 1\t1\t1\te "],
            ['WARNING', ' 1 ', " 1\t1\t1. "],
            ['WARNING', ' 1 ', "\n"],
            ['WARNING', ' 1 ', 'some error'],
            ['WARNING', "\n", " 0.1\t0.1\t0.1 "],
            ['WARNING', 'some error', " 0.1\t0.1\t0.1 "],
        ];
    }

    private function getHcService(): HealthCheckService
    {
        $acsrMock = $this->createMock(ArtisanVolatileDataRepository::class);

        return new HealthCheckService($acsrMock, self::HC_VALUES);
    }
}
