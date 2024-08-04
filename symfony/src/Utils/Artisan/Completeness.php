<?php

namespace App\Utils\Artisan;

use App\Utils\Traits\UtilityClass;
use App\Utils\Artisan\SmartAccessDecorator as Creator;

final class Completeness
{
    use UtilityClass;

    private const PERFECT = 100;
    private const GREAT = 80;
    private const GOOD = 65;
    private const OK = 50;

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
