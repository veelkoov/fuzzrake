<?php

declare(strict_types=1);

namespace App\DataDefinitions;

use App\Utils\Traits\EnumUtils;

enum Ages: string
{
    use EnumUtils;

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
}
