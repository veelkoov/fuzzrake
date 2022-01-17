<?php

declare(strict_types=1);

namespace App\DataDefinitions;

use App\Utils\Arrays;

enum Ages : string
{
    case MINORS = 'MINORS'; // grep-const-ages-minors
    case MIXED = 'MIXED'; // grep-const-ages-mixed
    case ADULTS = 'ADULTS'; // grep-const-ages-adults

    public function getLabel(): string
    {
        return match ($this) {
            self::MINORS => 'Everyone is under 18',
            self::MIXED  => 'There is a mix of people over and under 18',
            self::ADULTS => 'Everyone is over 18',
        };
    }

    public static function getChoices(bool $includeUnknown): array
    {
        $result = Arrays::assoc(array_map(fn ($item): array => [$item->getLabel(), $item->value], self::cases()));

        if ($includeUnknown) {
            $result['Unknown'] = null;
        }

        return $result;
    }

    public static function get(?string $value): ?Ages
    {
        return null === $value ? null : Ages::from($value);
    }
}
