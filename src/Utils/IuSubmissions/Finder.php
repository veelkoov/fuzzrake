<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Utils\Traits\UtilityClass;
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\Finder\Finder as FileFinder;

final class Finder
{
    use UtilityClass;

    /**
     * @return IuSubmission[]
     */
    public static function getFrom(string $directoryPath, ?DateTimeImmutable $onlyAfter = null): array
    {
        if (!is_dir($directoryPath)) {
            throw new InvalidArgumentException("Directory '$directoryPath' does not exist");
        }

        $result = [];

        $finder = new FileFinder();
        $finder->files()->in($directoryPath)->sortByName();

        foreach ($finder as $file) {
            $item = IuSubmission::fromFile($file);

            if (null === $onlyAfter || $item->getTimestamp() >= $onlyAfter) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
