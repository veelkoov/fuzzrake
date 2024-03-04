<?php

declare(strict_types=1);

namespace App\IuHandling;

use App\Data\Definitions\Fields\Field;
use App\Utils\Enforce;

final class SchemaFixer
{
    private const SCHEMA_VERSION = 'SCHEMA_VERSION';
    private const CURRENT_SCHEMA_VERSION = 13;

    /**
     * @param array<string, psJsonFieldValue> $data
     *
     * @return array<string, psJsonFieldValue>
     */
    public function fix(array $data): array
    {
        $data = self::assureVersionFieldExists($data);

        switch ($data[self::SCHEMA_VERSION]) {
            case 8:
                $data[Field::URL_PRICES->name] = [Enforce::string($data[Field::URL_PRICES->name])];
                $data[Field::URL_COMMISSIONS->name] = [Enforce::string($data['URL_CST'])];
                // no break

            case 9:
                $data[Field::IS_MINOR->name] = null;
                $data[Field::WORKS_WITH_MINORS->name] = null;
                // no break

            case 10:
                $data[Field::AGES->name] = null;
                // no break

            case 11:
                $data[Field::PAYMENT_PLANS->name] = explode("\n", Enforce::string($data[Field::PAYMENT_PLANS->name]));
                // no break

            case 12:
                $data[Field::NSFW_WEBSITE->name] = null;
                $data[Field::NSFW_SOCIAL->name] = null;
                $data[Field::DOES_NSFW->name] = null;
                // no break

            case 13:
                unset($data['BP_LAST_CHECK']);

                if (!array_key_exists(Field::URL_MASTODON->name, $data)) {
                    $data[Field::URL_MASTODON->name] = '';
                }
        }

        return $data;
    }

    /**
     * @param array<string, psJsonFieldValue> $data
     *
     * @return array<string, psJsonFieldValue>
     */
    private function assureVersionFieldExists(array $data): array
    {
        if (!array_key_exists(self::SCHEMA_VERSION, $data)) {
            $data[self::SCHEMA_VERSION] = 8;
        }

        return $data;
    }

    /**
     * @param array<string, psJsonFieldValue> $data
     *
     * @return array<string, psJsonFieldValue>
     */
    public static function appendSchemaVersion(array $data): array
    {
        $data[self::SCHEMA_VERSION] = self::CURRENT_SCHEMA_VERSION;

        return $data;
    }
}
