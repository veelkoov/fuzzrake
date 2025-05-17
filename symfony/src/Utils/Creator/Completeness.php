<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;

final class Completeness
{
    use UtilityClass;

    private const int PERFECT = 100;
    private const int GREAT = 80;
    private const int GOOD = 65;
    private const int OK = 50;

    public static function getCompletenessText(Creator $creator): string
    {
        $completeness = $creator->getCompleteness();

        if ($completeness >= self::PERFECT) {
            return 'Awesome! ❤️';
        } elseif ($completeness >= self::GREAT) {
            return 'Great!';
        } elseif ($completeness >= self::GOOD) {
            return 'Good job!';
        } elseif ($completeness >= self::OK) {
            return 'Some updates might be helpful...';
        } else {
            return 'Yikes! :( Updates needed!';
        }
    }

    public static function hasGood(Creator $creator): bool
    {
        return $creator->getCompleteness() >= self::GOOD;
    }
}
