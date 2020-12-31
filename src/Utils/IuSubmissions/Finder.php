<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Utils\DataInputException;
use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\Finder\Finder as FileFinder;

final class Finder
{
    use UtilityClass;

    /**
     * @return IuSubmission[]
     *
     * @throws JsonException|DataInputException
     */
    public static function getFrom(string $directoryPath): array
    {
        if (!is_dir($directoryPath)) {
            throw new InvalidArgumentException("Directory '$directoryPath' does not exist");
        }

        $result = [];

        $finder = new FileFinder();
        $finder->files()->in($directoryPath)->sortByName();

        foreach ($finder as $file) {
            $result[] = IuSubmission::fromFile($file);
        }

        return $result;
    }
}
