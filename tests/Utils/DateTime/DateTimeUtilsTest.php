<?php

declare(strict_types=1);

namespace App\Tests\Utils\DateTime;

use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use PHPUnit\Framework\TestCase;

class DateTimeUtilsTest extends TestCase
{
    public function testGetNowUtcUsesUtcTimeZoneType3(): void
    {
        $subject = DateTimeUtils::getNowUtc()->getTimezone();

        self::assertEquals('UTC', $subject->getName());
    }

    /**
     * @throws DateTimeException
     */
    public function testGetUtcAtUsesUtcTimeZoneType3(): void
    {
        $subject = DateTimeUtils::getUtcAt('2022-01-07 13:01')->getTimezone();

        self::assertEquals('UTC', $subject->getName());
    }
}
