<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanCommissionsStatusRepository;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Parse;
use App\Utils\ParseException;
use Doctrine\ORM\UnexpectedResultException;

class HealthCheckService
{
    private const WARNING = 'WARNING';
    private const OK = 'OK';

    public const MEMORY_AVAILABLE_MIN_MIBS = 'MEMORY_AVAILABLE_MIN_MIBS';
    public const DISK_FREE_MIN_MIBS = 'DISK_FREE_MIN_MIBS';
    public const DISK_USED_MAX_PERCENT = 'DISK_USED_MAX_PERCENT';
    public const LOAD_1M_MAX = 'LOAD_1M_MAX';
    public const LOAD_5M_MAX = 'LOAD_5M_MAX';
    public const LOAD_15M_MAX = 'LOAD_15M_MAX';

    private int $memoryAvailableMinMibs;
    private int $diskFreeMinMibs;
    private int $diskUsedMaxPercent;
    private float $load1mMax;
    private float $load5mMax;
    private float $load15mMax;

    public function __construct(
        private ArtisanCommissionsStatusRepository $artisanCommissionsStatusRepository,
        array $healthCheckValues,
    ) {
        $this->memoryAvailableMinMibs = $healthCheckValues[self::MEMORY_AVAILABLE_MIN_MIBS];
        $this->diskFreeMinMibs = $healthCheckValues[self::DISK_FREE_MIN_MIBS];
        $this->diskUsedMaxPercent = $healthCheckValues[self::DISK_USED_MAX_PERCENT];
        $this->load1mMax = $healthCheckValues[self::LOAD_1M_MAX];
        $this->load5mMax = $healthCheckValues[self::LOAD_5M_MAX];
        $this->load15mMax = $healthCheckValues[self::LOAD_15M_MAX];
    }

    public function getStatus(): array
    {
        return [
            'status'        => self::OK,
            'cstStatus'     => $this->getCstStatus(),
            'lastCstRunUtc' => $this->getLastCstRunUtc(),
            'serverTimeUtc' => $this->getServerTimeUtc(),
            'disk'          => $this->getDiskStatus(HealthCheckService::getDfRawOutput()),
            'memory'        => $this->getMemoryStatus(HealthCheckService::getMemoryAvailableRawOutput()),
            'load'          => $this->getLoadStatus($this->getCpuCountRawOutput(), HealthCheckService::getProcLoadAvgRawOutput()),
        ];
    }

    private function getCstStatus(): string
    {
        try {
            return $this->artisanCommissionsStatusRepository->getLastCstUpdateTime() < DateTimeUtils::getUtcAt('-12:15')
                ? self::OK
                : self::WARNING;
        } catch (DateTimeException | UnexpectedResultException) {
            return self::WARNING;
        }
    }

    private function getLastCstRunUtc(): string
    {
        return $this->artisanCommissionsStatusRepository->getLastCstUpdateTimeAsString();
    }

    private function getServerTimeUtc(): string
    {
        return DateTimeUtils::getNowUtc()->format('Y-m-d H:i:s');
    }

    public function getDiskStatus(string $rawData): string
    {
        $disks = explode("\n", trim($rawData));

        if (empty($disks)) {
            return self::WARNING;
        }

        foreach ($disks as $disk) {
            $disk = explode("\t", $disk);

            if (2 != count($disk)) {
                return self::WARNING;
            }

            try {
                $mibsFree = Parse::tInt($disk[0]);
                $percentUsed = Parse::tPercentAsInt($disk[1]);
            } catch (ParseException) {
                return self::WARNING;
            }

            if ((int) $mibsFree < $this->diskFreeMinMibs && $percentUsed > $this->diskUsedMaxPercent) {
                return self::WARNING;
            }
        }

        return self::OK;
    }

    public function getMemoryStatus(string $rawData): string
    {
        try {
            $memoryAvailableMibs = Parse::tInt($rawData);

            return $memoryAvailableMibs > $this->memoryAvailableMinMibs ? self::OK : self::WARNING;
        } catch (ParseException) {
            return self::WARNING;
        }
    }

    public function getLoadStatus(string $cpuCountRawData, string $procLoadAvgRawData): string
    {
        try {
            $cpuCount = Parse::tFloat($cpuCountRawData);
            $loads = explode("\t", trim($procLoadAvgRawData));

            if (3 != count($loads)) {
                return self::WARNING;
            }

            $load1m = Parse::tFloat($loads[0]) / $cpuCount;
            $load5m = Parse::tFloat($loads[1]) / $cpuCount;
            $load15m = Parse::tFloat($loads[2]) / $cpuCount;
        } catch (ParseException) {
            return self::WARNING;
        }

        return $load1m < $this->load1mMax && $load5m < $this->load5mMax && $load15m < $this->load15mMax
            ? self::OK
            : self::WARNING;
    }

    private function getCpuCountRawOutput(): string
    {
        return `lscpu 2>&1 | awk '/^CPU\(s\):/ { print $2 }' 2>&1` ?? '';
    }

    private static function getProcLoadAvgRawOutput(): string
    {
        return `awk '{ print $1 "\t" $2 "\t" $3}' 2>&1 < /proc/loadavg` ?? '';
    }

    private static function getDfRawOutput(): string
    {
        return `df -B 1M 2>&1 | awk '/^\/dev\// { print $4 "\t" $5 }' 2>&1` ?? '';
    }

    private static function getMemoryAvailableRawOutput(): string
    {
        return `free -m 2>&1 | awk '/^Mem:/ { print $7 }' 2>&1` ?? '';
    }
}
