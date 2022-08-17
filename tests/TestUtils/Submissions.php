<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\IuSubmissions\IuSubmissionService;
use App\Utils\IuSubmissions\LocalStorageService;
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
    public static function submit(Artisan $artisan): void
    {
        self::$storage ??= new LocalStorageService(Paths::getTestIuFormDataPath());

        self::$storage->saveOnDiskGetRelativePath(IuSubmissionService::asJson($artisan));
    }
}
