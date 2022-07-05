<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web;

use App\Utils\DateTime\UtcClockForTests;
use App\Utils\Web\FreeUrl;
use App\Utils\Web\HostCallsTiming;
use App\Utils\Web\TimedUrlQueue;
use PHPUnit\Framework\TestCase;

class TimedUrlQueueTest extends TestCase
{
    protected function setUp(): void
    {
        UtcClockForTests::start();
    }

    protected function tearDown(): void
    {
        UtcClockForTests::finish();
    }

    public function testPop(): void
    {
        $timing = new HostCallsTiming(1000);
        $urls = [
            new FreeUrl('https://two-host.example.com/abcd'),
            new FreeUrl('https://3rd-host.example.com/asdf'),
            new FreeUrl('https://one-host.example.com/abcd'),
            new FreeUrl('https://3rd-host.example.com/qwer'),
            new FreeUrl('https://two-host.example.com/qwer'),
            new FreeUrl('https://3rd-host.example.com/zxcv'),
        ];

        $subject = new TimedUrlQueue($urls, $timing);

        $actual = $subject->pop();
        self::assertNotNull($actual);
        self::assertStringStartsWith('https://3rd-host.example.com', $actual->getUrl());
        $timing->called('3rd-host.example.com');

        UtcClockForTests::passMs(10);

        $actual = $subject->pop();
        self::assertNotNull($actual);
        self::assertStringStartsWith('https://two-host.example.com', $actual->getUrl());
        $timing->called('two-host.example.com');

        UtcClockForTests::passMs(10);

        $actual = $subject->pop();
        self::assertNotNull($actual);
        self::assertStringStartsWith('https://one-host.example.com', $actual->getUrl());
        $timing->called('one-host.example.com');

        UtcClockForTests::passMs(10);

        $actual = $subject->pop();
        self::assertNotNull($actual);
        self::assertStringStartsWith('https://3rd-host.example.com', $actual->getUrl());
        $timing->called('3rd-host.example.com');

        UtcClockForTests::passMs(10);

        $actual = $subject->pop();
        self::assertNotNull($actual);
        self::assertStringStartsWith('https://two-host.example.com', $actual->getUrl());
        $timing->called('two-host.example.com');

        UtcClockForTests::passMs(10);

        $actual = $subject->pop();
        self::assertNotNull($actual);
        self::assertStringStartsWith('https://3rd-host.example.com', $actual->getUrl());
        $timing->called('3rd-host.example.com');
    }
}