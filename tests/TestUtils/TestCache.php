<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Utils\Traits\UtilityClass;
use Symfony\Component\Filesystem\Filesystem;

final class TestCache
{
    use UtilityClass;

    public static function clear(): void
    {
        (new Filesystem())->remove(Paths::getTestCacheDir());
    }
}
