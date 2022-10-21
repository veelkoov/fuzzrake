<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\IuHandling\Import\SubmissionData;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class SubmissionDataTest extends TestCase
{
    public function testGetIdFromFilePath(): void
    {
        $input = 'some-directory/2022/09/01/22:09:15_2545.json';
        $result = SubmissionData::getIdFromFilePath($input);

        self::assertEquals('2022-09-01_220915_2545', $result);
    }

    public function testGetFilePathFromId(): void
    {
        $input = '2022-09-01_220915_2545';
        $result = SubmissionData::getFilePathFromId($input);

        self::assertEquals('2022/09/01/22:09:15_2545.json', $result);
    }
}
