<?php

declare(strict_types=1);

namespace App\Utils\DataInput;

use App\Utils\Json;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\Finder\Finder;

abstract class JsonFinder
{
    /**
     * @return array[]
     * @throws JsonException
     */
    public static function arrayFromFiles(string $directoryPath): array
    {
        if (!is_dir($directoryPath)) {
            throw new InvalidArgumentException("Directory '$directoryPath' does not exist");
        }

        $result = [];

        $finder = new Finder();
        $finder->files()->in($directoryPath);

        foreach ($finder as $file) {
            $result[] = Json::decode($file->getContents());
        }

        return $result;
    }
}
