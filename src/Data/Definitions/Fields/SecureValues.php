<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Utils\Traits\UtilityClass;

final class SecureValues
{
    use UtilityClass;

    public const array FIELDS_HIDDEN_IN_CHANGES_DESCRIPTION = [
        Field::PASSWORD,
        Field::EMAIL_ADDRESS,
        Field::URL_MINIATURES,
        Field::DATE_ADDED,
        Field::DATE_UPDATED,
    ];

    public static function hideOnAdminScreen(Field $field): bool
    {
        return Field::PASSWORD === $field;
    }

    public static function hideInChangesDescription(Field $field): bool
    {
        return arr_contains(self::FIELDS_HIDDEN_IN_CHANGES_DESCRIPTION, $field);
    }
}
