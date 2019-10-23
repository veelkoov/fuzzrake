<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanCommissionsStatusRepository;
use App\Utils\DateTimeUtils;

class HealthCheckService
{
    private const WARNING = 'WARNING';
    private const OK = 'OK';

    /**
     * @var ArtisanCommissionsStatusRepository
     */
    private $artisanCommissionsStatusRepository;

    public function __construct(ArtisanCommissionsStatusRepository $acsr)
    {
        $this->artisanCommissionsStatusRepository = $acsr;
    }

    public function getStatus(): array
    {
        return [
            'status'        => self::OK,
            'lastCstRunUtc' => $this->getLastCstRunUtc(),
            'serverTimeUtc' => $this->getServerTimeUtc(),
            'disk'          => $this->getDiskStatus($this->readDisksData()),
            'memory'        => $this->getMemoryStatus($this->readMemoryData()),
            'load'          => $this->getLoadStatus(...$this->readLoadData()),
        ];
    }

    private function getLastCstRunUtc(): string
    {
        return $this->artisanCommissionsStatusRepository->getLastCstUpdateTimeAsString();
    }

    private function getServerTimeUtc(): string
    {
        return DateTimeUtils::getNowUtc()->format('Y-m-d H:i:s');
    }

    public function getDiskStatus(array $disksData): string
    {
        foreach ($disksData as $disk) {
            list($kilobytesFree, $percentUsed) = $disk;

            if ((int) $kilobytesFree < 1024 * 1024 && $percentUsed > 90) {
                return self::WARNING;
            }
        }

        return self::OK;
    }

    public function getMemoryStatus(int $megabytesFree): string
    {
        return $megabytesFree > 256 ? self::OK : self::WARNING;
    }

    public function getLoadStatus(int $load1m, int $load5m, int $load15m): string
    {
        return self::OK; // FIXME
    }

    /**
     * @return array[] [ [ kilobytesFree, percentUsed ], ... ]
     */
    private function readDisksData(): array
    {
        $rawData = `df | awk '/^\/dev\// { print $4 "\t" $5 }'`;

        return array_map(function (string $disk): array {
            list($kilobytesFree, $percentUsed) = explode("\t", $disk);

            return [(int) $kilobytesFree, (int) rtrim($percentUsed, '%')];
        }, explode("\n", trim($rawData)));
    }

    private function readMemoryData(): int
    {
        return 512; // FIXME
    }

    /**
     * @return int[]
     */
    private function readLoadData(): array
    {
        return [0, 0, 0]; // FIXME
    }
}
