<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanCommissionsStatusRepository;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Parse;
use App\Utils\ParseException;
use Doctrine\ORM\NonUniqueResultException;

class HealthCheckService // TODO: Move hardcoded values to parameters/.env
{
    private const WARNING = 'WARNING';
    private const OK = 'OK';

    private const MEMORY_FREE_MIN_MIBS = 256;
    const DISK_FREE_MIN_MIBS = 1024;
    const DISK_USED_MAX_PERCENT = 90;
    const LOAD_1M_MAX = 0.9;
    const LOAD_5M_MAX = 0.5;
    const LOAD_15M_MAX = 0.2;

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
            'cstStatus'     => $this->getCstStatus(),
            'lastCstRunUtc' => $this->getLastCstRunUtc(),
            'serverTimeUtc' => $this->getServerTimeUtc(),
            'disk'          => $this->getDiskStatus(HealthCheckService::getDfRawOutput()),
            'memory'        => $this->getMemoryStatus(HealthCheckService::getFreeRawOutput()),
            'load'          => $this->getLoadStatus($this->getCpuCountRawOutput(), HealthCheckService::getProcLoadAvgRawOutput()),
        ];
    }

    private function getCstStatus(): string
    {
        try {
            return $this->artisanCommissionsStatusRepository->getLastCstUpdateTime() < DateTimeUtils::getUtcAt('-12:15')
                ? self::OK
                : self::WARNING;
        } catch (DateTimeException | NonUniqueResultException $e) {
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
            } catch (ParseException $e) {
                return self::WARNING;
            }

            if ((int) $mibsFree < self::DISK_FREE_MIN_MIBS && $percentUsed > self::DISK_USED_MAX_PERCENT) {
                return self::WARNING;
            }
        }

        return self::OK;
    }

    public function getMemoryStatus(string $rawData): string
    {
        try {
            $memoryFreeMibs = Parse::tInt($rawData);

            return $memoryFreeMibs > self::MEMORY_FREE_MIN_MIBS ? self::OK : self::WARNING;
        } catch (ParseException $e) {
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
        } catch (ParseException $e) {
            return self::WARNING;
        }

        return $load1m < self::LOAD_1M_MAX && $load5m < self::LOAD_5M_MAX && $load15m < self::LOAD_15M_MAX
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

    private static function getFreeRawOutput(): string
    {
        return `free -m 2>&1 | awk '/^Mem:/ { print $7 }' 2>&1` ?? '';
    }
}
