<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\ArtisanCommissionsStatusRepository;
use App\Service\HealthCheckService;
use PHPUnit\Framework\TestCase;

class HealthCheckServiceTest extends TestCase
{
    /**
     * @dataProvider getDiskStatusDataProvider
     *
     * @param string $result
     * @param array  $disksData
     */
    public function testGetDiskStatus(string $result, array $disksData): void
    {
        static::assertEquals($result, $this->getHcService()->getDiskStatus($disksData));
    }

    public function getDiskStatusDataProvider()
    {
        return [
            [
                'OK',
                [
                    [1024 * 1024, 80],
                    [1024 * 1024, 80],
                ],
            ],
            [
                'OK',
                [
                    [1024 * 1024, 80],
                    [1024 * 1024, 95],
                ],
            ],
            [
                'OK',
                [
                    [1024 * 1024, 80],
                    [1024 * 1023, 90],
                ],
            ],
            [
                'WARNING',
                [
                    [1024 * 1024, 80],
                    [1024 * 1023, 91],
                ],
            ],
        ];
    }

    private function getHcService(): HealthCheckService
    {
        $acsrMock = $this->createMock(ArtisanCommissionsStatusRepository::class);

        /* @noinspection PhpParamsInspection */
        return new HealthCheckService($acsrMock);
    }
}
