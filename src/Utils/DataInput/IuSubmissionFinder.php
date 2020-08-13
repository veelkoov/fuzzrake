<?php

declare(strict_types=1);

namespace App\Utils\DataInput;

use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\Finder\Finder;

final class IuSubmissionFinder
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

        $finder = new Finder();
        $finder->files()->in($directoryPath);

        foreach ($finder as $file) {
            $result[] = new IuSubmission($file);
        }

        return $result;
    }
}
