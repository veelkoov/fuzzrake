<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Tests\TestUtils\Paths;
use Symfony\Component\Filesystem\Filesystem;

trait CacheTrait
{
    protected function clearCache(): void
    {
        new Filesystem()->remove(Paths::getCachePoolsDir());
    }
}
