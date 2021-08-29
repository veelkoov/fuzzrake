<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\DataDefinitions\Fields;
use App\Utils\Traits\Singleton;

final class SchemaFixer
{
    use Singleton;

    private const SCHEMA_VERSION = 'SCHEMA_VERSION';
    private const CURRENT_SCHEMA_VERSION = 9;

    public function fix(array $data): array
    {
        $data = self::assureVersionFieldExists($data);

        switch ($data[self::SCHEMA_VERSION]) {
            case 8:
                $data[Fields::BP_LAST_CHECK] = 'unknown';
                $data[Fields::URL_PRICES] = [$data[Fields::URL_PRICES]];
                $data[Fields::URL_COMMISSIONS] = [$data['URL_CST']];
        }

        return $data;
    }

    private function assureVersionFieldExists(array $data): array
    {
        if (!array_key_exists(self::SCHEMA_VERSION, $data)) {
            $data[self::SCHEMA_VERSION] = 8;
        }

        return $data;
    }

    public static function appendSchemaVersion(array $data): array
    {
        $data[self::SCHEMA_VERSION] = self::CURRENT_SCHEMA_VERSION;

        return $data;
    }
}
