<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Utils\Artisan\Fields;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Traits\Singleton;
use DateTimeInterface;

final class SchemaFixer
{
    use Singleton;

    private const SCHEMA_VERSION = 'SCHEMA_VERSION';
    private const CURRENT_SCHEMA_VERSION = 8;

    private DateTimeInterface $v7releaseTimestamp;

    /**
     * @throws DateTimeException
     */
    private function __construct()
    {
        $this->v7releaseTimestamp = DateTimeUtils::getUtcAt('2020-08-15 00:00:00');
    }

    public function fix(array $data, DateTimeInterface $timestamp): array
    {
        $data = self::assureVersionFieldExists($data, $timestamp);

        switch ($data[self::SCHEMA_VERSION]) {
            /* @noinspection PhpMissingBreakStatementInspection */
            case 6:
                $data[Fields::URL_FURTRACK] = '';
                $data[Fields::URL_MINIATURES] = $data['URL_SCRITCH_MINIATURE'];
                $data[Fields::URL_PHOTOS] = $data['URL_SCRITCH_PHOTO'];
                // no break - deliberate fall-through

            case 7:
                $data[Fields::URL_PRICES] = [$data[Fields::URL_PRICES]];
                $data[Fields::URL_COMMISSIONS] = [$data[Fields::URL_COMMISSIONS]];
                $data[Fields::BP_LAST_CHECK] = 'unknown';
        }

        return $data;
    }

    private function assureVersionFieldExists(array $data, DateTimeInterface $timestamp): array
    {
        if (!array_key_exists(self::SCHEMA_VERSION, $data)) {
            $data[self::SCHEMA_VERSION] = $timestamp < $this->v7releaseTimestamp ? 6 : 7;
        }

        return $data;
    }

    public static function appendSchemaVersion(array $data): array
    {
        $data[self::SCHEMA_VERSION] = self::CURRENT_SCHEMA_VERSION;

        return $data;
    }
}
