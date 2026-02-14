<?php

declare(strict_types=1);

namespace App\Utils\Traits;

use Veelkoov\Debris\Maps\StringToNullString;

trait EnumUtils
{
    public static function getFormChoices(bool $includeUnknown): StringToNullString
    {
        $result = new StringToNullString();

        if ($includeUnknown) {
            $result->set('Unknown', null);
        }

        foreach (static::cases() as $case) {
            $result->set($case->getLabel(), $case->value);
        }

        return $result;
    }

    public static function get(?string $value): ?static
    {
        return null === $value ? null : static::from($value);
    }
}
