<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Utils\Artisan\Fields;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Traits\Singleton;
use DateTimeInterface;

class SchemaFixer
{
    use Singleton;

    private const SCHEMA_VERSION = 'SCHEMA_VERSION';
    private const SCHEMA_V6 = 'SCHEMA_V6';
    private const SCHEMA_V7 = 'SCHEMA_V7';
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
            case self::SCHEMA_V6:
                $data[Fields::URL_FURTRACK] = '';
                $data[Fields::URL_MINIATURES] = $data['URL_SCRITCH_MINIATURE'];
                $data[Fields::URL_PHOTOS] = $data['URL_SCRITCH_PHOTO'];
                // Deliberate fall-through

            case self::SCHEMA_V7:
                $data[Fields::URL_PRICES] = [$data[Fields::URL_PRICES]];
                $data[Fields::URL_COMMISSIONS] = [$data[Fields::URL_COMMISSIONS]];
                $data[Fields::BP_LAST_CHECK] = 'unknown';
        }

        return $data;
    }

    private function assureVersionFieldExists(array $data, DateTimeInterface $timestamp): array
    {
        if ($timestamp < $this->v7releaseTimestamp) {
            $data[self::SCHEMA_VERSION] = self::SCHEMA_V6;
        } else {
            $data[self::SCHEMA_VERSION] = self::SCHEMA_V7;
        }

        return $data;
    }
}
