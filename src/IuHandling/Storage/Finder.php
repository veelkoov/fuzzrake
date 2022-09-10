<?php

declare(strict_types=1);

namespace App\IuHandling\Storage;

use App\IuHandling\Import\SubmissionData;
use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder as FileFinder;

final class Finder
{
    use UtilityClass;

    /**
     * @param positive-int $limit
     *
     * @return SubmissionData[]
     */
    public static function getFrom(string $directoryPath, int $limit, bool $reverse = false): array
    {
        if (!is_dir($directoryPath)) {
            throw new InvalidArgumentException("Directory '$directoryPath' does not exist");
        }

        $result = [];

        foreach (self::getFinder($directoryPath, $reverse) as $file) {
            if (0 === $limit--) {
                break;
            }

            $result[] = SubmissionData::fromFile($file);
        }

        return $result;
    }

    private static function getFinder(string $directoryPath, bool $reverse): FileFinder
    {
        $finder = new FileFinder();
        $finder->files()->in($directoryPath)->sortByName();

        if ($reverse) {
            $finder->reverseSorting();
        }

        return $finder;
    }
}
