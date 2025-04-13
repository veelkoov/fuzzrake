<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Traits\UtilityClass;

final class SecureValues
{
    use UtilityClass;

    public static function forIuForm(Artisan $artisan): void
    {
        $artisan->setPassword('');
        $artisan->setEmailAddress('');
    }

    public static function hideOnAdminScreen(Field $field): bool
    {
        return Field::PASSWORD === $field;
    }

    public static function hideInChangesDescription(Field $field): bool
    {
        return in_array($field, [Field::PASSWORD, Field::EMAIL_ADDRESS, Field::URL_MINIATURES, Field::DATE_ADDED, Field::DATE_UPDATED]);
    }
}
