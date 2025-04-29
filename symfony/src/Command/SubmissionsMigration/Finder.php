<?php

declare(strict_types=1);

namespace App\Command\SubmissionsMigration;

use App\Utils\Traits\UtilityClass;
use Generator;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder as FileFinder;

final class Finder
{
    use UtilityClass;

    /**
     * @return Generator<string>
     */
    public static function getFrom(string $directoryPath): Generator
    {
        if (!is_dir($directoryPath)) {
            throw new InvalidArgumentException("Directory '$directoryPath' does not exist");
        }

        foreach (self::getFinder($directoryPath) as $file) {
            yield SubmissionData::getIdFromFilePath($file->getRelativePathname());
        }
    }

    private static function getFinder(string $directoryPath): FileFinder
    {
        return (new FileFinder())->files()->in($directoryPath)
            ->sortByName()
            ->reverseSorting();
    }
}
