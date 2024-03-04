<?php

declare(strict_types=1);

namespace App\Utils\Traits;

trait EnumUtils
{
    /**
     * @return array<string, string|null>
     */
    public static function getChoices(bool $includeUnknown): array
    {
        $result = [];

        if ($includeUnknown) {
            $result['Unknown'] = null;
        }

        foreach (static::cases() as $case) {
            $result[$case->getLabel()] = $case->value;
        }

        return $result;
    }

    public static function get(?string $value): ?static
    {
        return null === $value ? null : static::from($value);
    }
}
