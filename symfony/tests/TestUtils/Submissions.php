<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\IuHandling\Import\SubmissionData;
use App\IuHandling\Storage\LocalStorageService;
use App\IuHandling\Submission\SubmissionService;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Json;
use Exception;
use JsonException;
use Symfony\Component\Filesystem\Filesystem;

class Submissions
{
    private static LocalStorageService $storage;

    public static function emptyTestSubmissionsDir(): void
    {
        (new Filesystem())->remove(Paths::getTestIuFormDataPath());
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public static function submit(Creator $creator): string
    {
        self::$storage ??= new LocalStorageService(Paths::getTestIuFormDataPath());

        $path = self::$storage->saveOnDiskGetRelativePath(SubmissionService::asJson($creator));

        return SubmissionData::getIdFromFilePath($path);
    }

    public static function from(Creator $creator): SubmissionData
    {
        /**
         * @var array<string, psJsonFieldValue> $data
         */
        $data = Json::decode(SubmissionService::asJson($creator));

        return new SubmissionData(UtcClock::now(), 'MOCK SUBMISSION', $data);
    }
}
