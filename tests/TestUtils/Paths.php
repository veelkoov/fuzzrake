<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Utils\Traits\UtilityClass;

/**
 * All the paths in tests ugliness brought all and in the darkness bound, or sth idk.
 */
final class Paths
{
    use UtilityClass;

    /**
     * @return non-empty-string
     */
    public static function getArtisanTypeScriptClassPath(): string
    {
        return __DIR__.'/../../assets/scripts/class/Artisan.ts';
    }

    /**
     * @return non-empty-string
     */
    public static function getTestIuFormDataPath(): string
    {
        return __DIR__.'/../../var/testIuFormData';
    }

    /**
     * @return non-empty-string
     */
    public static function getCompletenessCalcClassPath(): string
    {
        return __DIR__.'/../../src/Utils/Artisan/CompletenessCalc.php';
    }

    /**
     * @return non-empty-string
     */
    public static function getDataDefinitionsPath(string $fileName): string
    {
        return __DIR__."/../../config/data_definitions/$fileName";
    }

    /**
     * @return non-empty-string
     */
    public static function getTestDataPath(string $fileName): string
    {
        return __DIR__."/../test_data/$fileName";
    }

    /**
     * @return non-empty-string
     */
    public static function getTestCacheDir(): string
    {
        return __DIR__.'/../../var/cache/test';
    }
}
