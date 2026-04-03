<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Utils\Traits\UtilityClass;
use RuntimeException;

/**
 * All the paths in tests ugliness brought all and in the darkness bound, or sth idk.
 */
final class Paths
{
    use UtilityClass;

    /**
     * @return non-empty-string
     */
    public static function getRepoTopDirPath(): string
    {
        $realpath = realpath(__DIR__.'/../..');

        return false !== $realpath ? $realpath : throw new RuntimeException('Failed getting repo\'s top dir path.');
    }

    /**
     * @return non-empty-string
     */
    public static function getDataDefinitionsPath(string $fileName): string
    {
        return self::getRepoTopDirPath()."/config/fuzzrake/$fileName";
    }

    /**
     * @return non-empty-string
     */
    public static function getTestDataPath(string $fileName): string
    {
        return self::getRepoTopDirPath()."/tests/test_data/$fileName";
    }

    /**
     * @return non-empty-string
     */
    public static function getCachePoolsDir(): string
    {
        return self::getRepoTopDirPath().'/var/cache/test/pools';
    }

    /**
     * @return non-empty-string
     */
    public static function getTestsDirPath(): string
    {
        return self::getRepoTopDirPath().'/tests';
    }

    /**
     * @return non-empty-string
     */
    public static function getSrcDirPath(): string
    {
        return self::getRepoTopDirPath().'/src';
    }
}
