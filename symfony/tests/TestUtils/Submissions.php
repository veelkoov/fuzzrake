<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\IuHandling\Import\SubmissionData;
use App\IuHandling\Storage\LocalStorageService;
use App\IuHandling\Submission\SubmissionService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
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
    public static function submit(Artisan $artisan): string
    {
        self::$storage ??= new LocalStorageService(Paths::getTestIuFormDataPath());

        $path = self::$storage->saveOnDiskGetRelativePath(SubmissionService::asJson($artisan));

        return SubmissionData::getIdFromFilePath($path);
    }

    public static function from(Artisan $artisan): SubmissionData
    {
        /**
         * @var array<string, psJsonFieldValue> $data
         */
        $data = Json::decode(SubmissionService::asJson($artisan));

        return new SubmissionData(UtcClock::now(), 'MOCK SUBMISSION', $data);
    }
}
