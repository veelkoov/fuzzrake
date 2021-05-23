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
    private const SCHEMA_V8 = 'SCHEMA_V8';
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
                // no break

            case self::SCHEMA_V7: // TODO: Drop support for older submissions to remove plaintext everywhere
                $data[Fields::PASSWORD] = $data['PASSCODE'];
                unset($data['PASSCODE']);
        }

        return $data;
    }

    private function assureVersionFieldExists(array $data, DateTimeInterface $timestamp): array
    {
        if ($timestamp < $this->v7releaseTimestamp) {
            $data[self::SCHEMA_VERSION] = self::SCHEMA_V6;
        } elseif (array_key_exists('PASSCODE', $data)) {
            $data[self::SCHEMA_VERSION] = self::SCHEMA_V7;
        } else {
            $data[self::SCHEMA_VERSION] = self::SCHEMA_V8;
        }

        return $data;
    }
}
