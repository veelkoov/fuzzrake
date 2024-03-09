<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Service\Cache;
use App\Utils\Traits\UtilityClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

final class CacheUtils
{
    use UtilityClass;

    public static function getArrayBased(): Cache
    {
        return new Cache(new TagAwareAdapter(new ArrayAdapter()));
    }
}
