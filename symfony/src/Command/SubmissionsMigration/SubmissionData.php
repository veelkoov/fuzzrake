<?php

declare(strict_types=1);

namespace App\Command\SubmissionsMigration;

use App\Utils\DataInputException;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\Traits\UtilityClass;
use DateTimeImmutable;

final class SubmissionData
{
    use UtilityClass;

    public static function getTimestampFromFilePath(string $filePath): DateTimeImmutable
    {
        $dateTimeStr = pattern('^(?:.*/)?(\d{4})/(\d{2})/(\d{2})/(\d{2}:\d{2}:\d{2})_\d{4}\.json$')
            ->replace($filePath)
            ->first()
            ->withReferences('$1-$2-$3 $4');

        try {
            return UtcClock::at($dateTimeStr);
        } catch (DateTimeException $exception) {
            throw new DataInputException("Couldn't parse the timestamp ('$dateTimeStr') out of the I/U submission file path: '$filePath'", previous: $exception);
        }
    }

    public static function getIdFromFilePath(string $filePath): string
    {
        return pattern('^(?:.*/)?(\d{4})/(\d{2})/(\d{2})/(\d{2}):(\d{2}):(\d{2})_(\d{4})\.json$')
            ->replace($filePath)
            ->first()
            ->withReferences('$1-$2-$3_$4$5$6_$7');
    }

    public static function getFilePathFromId(string $id): string
    {
        return pattern('^(\d{4})-(\d{2})-(\d{2})_(\d{2})(\d{2})(\d{2})_(\d{4})$')
            ->replace($id)
            ->first()
            ->withReferences('$1/$2/$3/$4:$5:$6_$7.json');
    }
}
