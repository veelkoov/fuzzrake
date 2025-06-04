<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Twig\ReadabilityExtensions;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class ReadabilityExtensionsTest extends TestCase
{
    private ReadabilityExtensions $subject;

    #[Override]
    public function setUp(): void
    {
        $this->subject = new ReadabilityExtensions();
    }

    #[DataProvider('eventUrlDataProvider')]
    public function testEventUrl(string $input, string $expected): void
    {
        self::assertSame($expected, $this->subject->eventUrl($input));
    }

    /**
     * @return array<array{string, string}>
     */
    public static function eventUrlDataProvider(): array
    {
        return [
            ['http://www.getfursu.it/', 'getfursu.it'],
            ['https://ww.getfursu.it/new', 'ww.getfursu.it/new'],
            ['https://www.furaffinity.net/user/lisoov/', 'furaffinity.net/u/lisoov'],
            ['furaffinity.net/journal/10373912/', 'furaffinity.net/j/10373912'],
            ['https://www.getfursu.it/34567890123456789012345678901234567890+', 'getfursu.it/3456789012345678901234567890...'],
        ];
    }

    #[DataProvider('humanFriendlyRegexpDataProvider')]
    public function testHumanFriendlyRegexp(string $input, string $expected): void
    {
        self::assertSame($expected, $this->subject->humanFriendlyRegexp($input));
    }

    /**
     * @return array<array{string, string}>
     */
    public static function humanFriendlyRegexpDataProvider(): array
    {
        return [
            ['PART(?!IAL)S', 'PARTS'],
            ['(?<![A-Z])LCD', 'LCD'],
            ['(LCD|LED|EL)', 'LCD|LED|EL'],
            ['BASES?|BLANKS?', 'BASES|BLANKS'],
        ];
    }
}
