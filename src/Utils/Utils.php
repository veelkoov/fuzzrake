<?php

namespace App\Utils;

use App\Entity\Artisan;

class Utils
{
    public static function artisanNames(Artisan $artisan1, Artisan $artisan2)
    {
        $names = array_unique(array_filter([
            $artisan1->getMakerId(),
            $artisan1->getName(),
            $artisan1->getFormerly(),
            $artisan2->getMakerId(),
            $artisan2->getName(),
            $artisan2->getFormerly(),
        ]));

        return implode(' / ', $names);
    }

    public static function safeStr(string $input): string
    {
        return str_replace(["\r", "\n", '\\'], ['\r', '\n', '\\'], $input);
    }

    public static function unsafeStr(string $input): string
    {
        return str_replace(['\r', '\n', '\\'], ["\r", "\n", '\\'], $input);
    }
}
