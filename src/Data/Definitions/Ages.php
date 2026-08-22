<?php

declare(strict_types=1);

namespace App\Data\Definitions;

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
            self::MINORS => 'All studio members are under 18',
            self::MIXED  => 'Mix of people over and under 18 in the studio',
            self::ADULTS => 'All studio members are over 18',
        };
    }
}
