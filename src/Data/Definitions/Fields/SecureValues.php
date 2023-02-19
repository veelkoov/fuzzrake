<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Traits\UtilityClass;
use UnexpectedValueException;

final class SecureValues
{
    use UtilityClass;

    public static function forIuForm(Artisan $artisan): void
    {
        $artisan->setPassword('');
    }

    /**
     * @param array<string, psFieldValue> $where
     */
    public static function forLogs(array &$where): void
    {
        self::replace(Field::PASSWORD, '[redacted]', $where);
        self::replace(Field::CONTACT_INFO_ORIGINAL, '[redacted]', $where);
        self::replace(Field::CONTACT_INFO_OBFUSCATED, '[redacted]', $where);
    }

    /**
     * @param array<string, psFieldValue> $where
     */
    public static function forSessionStorage(array &$where): void
    {
        self::replace(Field::PASSWORD, '', $where);
    }

    public static function hideOnAdminScreen(Field $field): bool
    {
        return Field::PASSWORD === $field;
    }

    public static function hideInChangesDescription(Field $field): bool
    {
        return in_array($field, [Field::PASSWORD, Field::CONTACT_METHOD, Field::CONTACT_INFO_ORIGINAL, Field::CONTACT_ADDRESS_PLAIN, Field::URL_MINIATURES, Field::DATE_ADDED, Field::DATE_UPDATED]);
    }

    /**
     * @param array<string, psFieldValue> $where
     */
    private static function replace(Field $what, string $with, array &$where): void
    {
        if (array_key_exists($what->value, $where)) {
            $where[$what->value] = $with;
        } elseif (array_key_exists($what->modelName(), $where)) {
            $where[$what->modelName()] = $with;
        } else {
            throw new UnexpectedValueException("Failed to replace $what->value in given data.");
        }
    }
}
