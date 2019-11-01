<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\ArtisanCommissionsStatusRepository;
use App\Service\HealthCheckService;
use DateTime;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\TestCase;

class HealthCheckServiceTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCstStatus(): void
    {
        $acsrMock = $this->createMock(ArtisanCommissionsStatusRepository::class);
        $acsrMock
            ->expects(self::exactly(2))
            ->method('getLastCstUpdateTime')
            ->willReturn(
                new DateTime('-12:10h', new DateTimeZone('UTC')),
                new DateTime('-12:20h', new DateTimeZone('UTC')))
        ;

        /* @noinspection PhpParamsInspection */
        $hcSrv = new HealthCheckService($acsrMock);

        static::assertEquals('OK', $hcSrv->getStatus()['cstStatus']);
        static::assertEquals('WARNING', $hcSrv->getStatus()['cstStatus']);
    }

    /**
     * @dataProvider getDiskStatusDataProvider
     *
     * @param string $result
     * @param string $rawInput
     */
    public function testGetDiskStatus(string $result, string $rawInput): void
    {
        static::assertEquals($result, $this->getHcService()->getDiskStatus($rawInput));
    }

    /**
     * @dataProvider getMemoryStatusDataProvider
     *
     * @param string $result
     * @param string $rawInput
     */
    public function testGetMemoryStatus(string $result, string $rawInput): void
    {
        static::assertEquals($result, $this->getHcService()->getMemoryStatus($rawInput));
    }

    /**
     * @dataProvider getLoadStatusDataProvider
     *
     * @param string $result
     * @param string $cpuCountRawData
     * @param string $procLoadAvgRawData
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
            ['WARNING', "some error"],
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
            ['WARNING', "\n",],
            ['WARNING', '512 e',],
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
            ['WARNING', ' 1 ', "some error"],
            ['WARNING', "\n", " 0.1\t0.1\t0.1 "],
            ['WARNING', "some error", " 0.1\t0.1\t0.1 "],
        ];
    }

    private function getHcService(): HealthCheckService
    {
        $acsrMock = $this->createMock(ArtisanCommissionsStatusRepository::class);

        /* @noinspection PhpParamsInspection */
        return new HealthCheckService($acsrMock);
    }
}
