<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Utils\Traits\UtilityClass;
use Symfony\Component\Yaml\Yaml;

final class DataDefinitions
{
    use UtilityClass;

    /**
     * @return mixed[]
     */
    public static function get(string $fileName, string $key): array
    {
        // @phpstan-ignore-next-line - Data structure
        return Yaml::parseFile(Paths::getDataDefinitionsPath($fileName))['parameters'][$key];
    }
}
