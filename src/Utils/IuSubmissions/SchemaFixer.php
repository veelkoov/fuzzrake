<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\DataDefinitions\Fields\Field;
use App\Utils\Traits\Singleton;

final class SchemaFixer
{
    use Singleton;

    private const SCHEMA_VERSION = 'SCHEMA_VERSION';
    private const CURRENT_SCHEMA_VERSION = 13;

    /**
     * @param array<string, psIuSubmissionFieldValue> $data
     *
     * @return array<string, psIuSubmissionFieldValue>
     */
    public function fix(array $data): array
    {
        $data = self::assureVersionFieldExists($data);

        switch ($data[self::SCHEMA_VERSION]) {
            case 8:
                $data[Field::URL_PRICES->name] = [$data[Field::URL_PRICES->name]];
                $data[Field::URL_COMMISSIONS->name] = [$data['URL_CST']];
                // no break

            case 9:
                $data[Field::IS_MINOR->name] = null;
                $data[Field::WORKS_WITH_MINORS->name] = null;
                // no break

            case 10:
                $data[Field::AGES->name] = null;
                // no break

            case 11:
                $data[Field::PAYMENT_PLANS->name] = explode("\n", $data[Field::PAYMENT_PLANS->name]);
                // no break

            case 12:
                $data[Field::NSFW_WEBSITE->name] = null;
                // no break

            case 13:
                unset($data['BP_LAST_CHECK']);
        }

        return $data;
    }

    /**
     * @param array<string, psIuSubmissionFieldValue> $data
     *
     * @return array<string, psIuSubmissionFieldValue>
     */
    private function assureVersionFieldExists(array $data): array
    {
        if (!array_key_exists(self::SCHEMA_VERSION, $data)) {
            $data[self::SCHEMA_VERSION] = 8;
        }

        return $data;
    }

    /**
     * @param array<string, psIuSubmissionFieldValue> $data
     *
     * @return array<string, psIuSubmissionFieldValue>
     */
    public static function appendSchemaVersion(array $data): array
    {
        $data[self::SCHEMA_VERSION] = self::CURRENT_SCHEMA_VERSION;

        return $data;
    }
}
