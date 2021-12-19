<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanVolatileDataRepository;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeFormat;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Parse;
use App\Utils\ParseException;
use DateTimeInterface;
use Doctrine\ORM\UnexpectedResultException;

class HealthCheckService
{
    private const WARNING = 'WARNING';
    private const OK = 'OK';

    final public const MEMORY_AVAILABLE_MIN_MIBS = 'MEMORY_AVAILABLE_MIN_MIBS';
    final public const DISK_FREE_MIN_MIBS = 'DISK_FREE_MIN_MIBS';
    final public const DISK_USED_MAX_PERCENT = 'DISK_USED_MAX_PERCENT';
    final public const LOAD_1M_MAX = 'LOAD_1M_MAX';
    final public const LOAD_5M_MAX = 'LOAD_5M_MAX';
    final public const LOAD_15M_MAX = 'LOAD_15M_MAX';

    private readonly int $memoryAvailableMinMibs;
    private readonly int $diskFreeMinMibs;
    private readonly int $diskUsedMaxPercent;
    private readonly float $load1mMax;
    private readonly float $load5mMax;
    private readonly float $load15mMax;

    public function __construct(
        private readonly ArtisanVolatileDataRepository $artisanVolatileDataRepository,
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
            'status'          => self::OK,
            'csUpdatesStatus' => $this->getCsUpdatesStatus(),
            'bpUpdatesStatus' => $this->getBpUpdatesStatus(),
            'lastCsUpdateUtc' => $this->getLastCsUpdateUtc(),
            'lastBpUpdateUtc' => $this->getLastBpUpdateUtc(),
            'serverTimeUtc'   => $this->getServerTimeUtc(),
            'disk'            => $this->getDiskStatus(HealthCheckService::getDfRawOutput()),
            'memory'          => $this->getMemoryStatus(HealthCheckService::getMemoryAvailableRawOutput()),
            'load'            => $this->getLoadStatus($this->getCpuCountRawOutput(), HealthCheckService::getProcLoadAvgRawOutput()),
        ];
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

            if ($mibsFree < $this->diskFreeMinMibs && $percentUsed > $this->diskUsedMaxPercent) {
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

    private function getCsUpdatesStatus(): string
    {
        try {
            return $this->getUpdatesStatus('-12 hours -15 minutes', $this->artisanVolatileDataRepository->getLastCsUpdateTime()); // grep-tracking-frequency
        } catch (DateTimeException|UnexpectedResultException) {
            return self::WARNING;
        }
    }

    private function getBpUpdatesStatus(): string
    {
        try {
            return $this->getUpdatesStatus('-7 days -15 minutes', $this->artisanVolatileDataRepository->getLastBpUpdateTime()); // grep-tracking-frequency
        } catch (DateTimeException|UnexpectedResultException) {
            return self::WARNING;
        }
    }

    /**
     * @throws DateTimeException
     */
    private function getUpdatesStatus(string $oldestAllowedCheck, DateTimeInterface $actualLastCheck): string
    {
        /* Need to format as string, because PHP doesn't allow comparing the mock object returned by PHPUnit from the repo mock (I guess) */
        $actual = $actualLastCheck->format(DATE_ISO8601);
        $oldestAllowed = DateTimeUtils::getUtcAt($oldestAllowedCheck)->format(DATE_ISO8601);

        return $actual > $oldestAllowed ? self::OK : self::WARNING;
    }

    private function getLastCsUpdateUtc(): string
    {
        try {
            $result = $this->artisanVolatileDataRepository->getLastCsUpdateTime();
        } catch (DateTimeException|UnexpectedResultException) {
            $result = null;
        }

        return DateTimeFormat::fragile($result);
    }

    private function getLastBpUpdateUtc(): string
    {
        try {
            $result = $this->artisanVolatileDataRepository->getLastBpUpdateTime();
        } catch (DateTimeException|UnexpectedResultException) {
            $result = null;
        }

        return DateTimeFormat::fragile($result);
    }

    private function getServerTimeUtc(): string
    {
        return DateTimeUtils::getNowUtc()->format('Y-m-d H:i:s');
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
