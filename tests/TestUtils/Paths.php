<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

/**
 * All the paths in tests ugliness brought all and in the darkness bound, or sth idk.
 */
class Paths
{
    public static function getArtisanTypeScriptClassPath(): string
    {
        return __DIR__.'/../../assets/scripts/class/Artisan.ts';
    }

    public static function getTestIuFormDataPath(): string
    {
        return __DIR__.'/../../var/testIuFormData'; // TODO: Compute using container stuff.
    }

    public static function getCompletenessCalcClassPath(): string
    {
        return __DIR__.'/../../src/Utils/Artisan/CompletenessCalc.php';
    }

    public static function getDataDefinitionsPath(string $fileName): string
    {
        return __DIR__."/../../config/data_definitions/$fileName";
    }

    public static function getTestDataPath(string $fileName): string
    {
        return __DIR__."/../test_data/$fileName";
    }
}
