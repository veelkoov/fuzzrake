<?php

declare(strict_types=1);

namespace App\DataDefinitions;

use App\Utils\Arrays;

enum Ages: string
{
    case MINORS = 'MINORS'; // grep-const-ages-minors
    case MIXED = 'MIXED'; // grep-const-ages-mixed
    case ADULTS = 'ADULTS'; // grep-const-ages-adults

    public function getLabel(): string
    {
        return match ($this) {
            self::MINORS => 'I am a (we all are) minor(s)/underage',
            self::MIXED  => 'The studio consists of both minors and adults',
            self::ADULTS => 'I am (all of us are) at least 18 years old',
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
}
