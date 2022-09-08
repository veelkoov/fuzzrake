<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\IuHandling\SubmissionData;
use App\Utils\Traits\UtilityClass;
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder as FileFinder;

final class Finder
{
    use UtilityClass;

    /**
     * @param ?positive-int $limit
     *
     * @return SubmissionData[]
     */
    public static function getFrom(string $directoryPath, ?DateTimeImmutable $onlyAfter = null, ?int $limit = null, bool $reverse = false): array
    {
        if (!is_dir($directoryPath)) {
            throw new InvalidArgumentException("Directory '$directoryPath' does not exist");
        }

        $result = [];

        foreach (self::getFinder($directoryPath, $reverse) as $file) {
            $item = SubmissionData::fromFile($file);

            if (self::isTooOld($onlyAfter, $item)) {
                continue;
            }

            if (null !== $limit && 0 === $limit--) {
                break;
            }

            $result[] = $item;
        }

        return $result;
    }

    private static function isTooOld(?DateTimeImmutable $onlyAfter, SubmissionData $item): bool
    {
        return null !== $onlyAfter && $item->getTimestamp() < $onlyAfter;
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
