<?php

declare(strict_types=1);

namespace App\IuHandling\Storage;

use App\IuHandling\Import\SubmissionData;
use App\Utils\Pagination\ItemsPage;
use App\Utils\Pagination\Pagination;
use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder as FileFinder;
use Symfony\Component\Finder\SplFileInfo;
use TRegx\CleanRegex\Exception\ReplacementExpectationFailedException;

final class Finder
{
    use UtilityClass;

    public const int PAGE_SIZE = Pagination::PAGE_SIZE;

    public static function getSingleFrom(string $directoryPath, string $id): ?SubmissionData
    {
        try {
            $relativeFilePath = SubmissionData::getFilePathFromId($id);
        } catch (ReplacementExpectationFailedException) {
            return null;
        }

        $absoluteFilePath = $directoryPath.'/'.$relativeFilePath;

        if (!file_exists($absoluteFilePath)) {
            return null;
        }

        return SubmissionData::fromFile(new SplFileInfo(
            $absoluteFilePath,
            dirname($relativeFilePath),
            $relativeFilePath,
        ));
    }

    /**
     * @param positive-int $page
     *
     * @return ItemsPage<SubmissionData>
     */
    public static function getFrom(string $directoryPath, int $page): ItemsPage
    {
        if (!is_dir($directoryPath)) {
            throw new InvalidArgumentException("Directory '$directoryPath' does not exist");
        }

        $result = [];

        $firstIdx = Pagination::getFirstIdx(self::PAGE_SIZE, $page);
        $lastIdx = $firstIdx + self::PAGE_SIZE - 1;
        $index = -1;

        $finder = self::getFinder($directoryPath);

        foreach ($finder as $file) {
            ++$index;

            if ($index < $firstIdx) {
                continue;
            }

            if ($index > $lastIdx) {
                break;
            }

            $result[] = SubmissionData::fromFile($file);
        }

        return new ItemsPage($result, $finder->count(), $page, Pagination::countPages($finder, self::PAGE_SIZE));
    }

    private static function getFinder(string $directoryPath): FileFinder
    {
        return (new FileFinder())->files()->in($directoryPath)
            ->sortByName()
            ->reverseSorting();
    }
}
