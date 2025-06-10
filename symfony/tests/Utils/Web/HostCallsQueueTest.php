<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web;

use App\Utils\DateTime\UtcClock;
use App\Utils\Web\HostCallsQueue;
use App\Utils\Web\Url\FreeUrl;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[Small]
class HostCallsQueueTest extends TestCase
{
    use ClockSensitiveTrait;

    public function testPatientlyProperlyDelaysCallsForSameHost(): void
    {
        self::mockTime();

        $subject = new HostCallsQueue(600);
        $now = UtcClock::time();

        // @phpstan-ignore staticMethod.alreadyNarrowedType (Testing interface/sanity check)
        self::assertSame(1, $subject->patiently(new FreeUrl('https://getfursu.it/'), static fn () => 1));
        self::assertSame($now, UtcClock::time());

        // @phpstan-ignore staticMethod.alreadyNarrowedType (Testing interface/sanity check)
        self::assertSame(2, $subject->patiently(new FreeUrl('http://127.0.0.1/'), static fn () => 2));
        self::assertSame($now, UtcClock::time());

        // @phpstan-ignore staticMethod.alreadyNarrowedType (Testing interface/sanity check)
        self::assertSame(3, $subject->patiently(new FreeUrl('http://getfursu.it/info'), static fn () => 3));
        self::assertSame($now + 600, UtcClock::time());
    }
}
