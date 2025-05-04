<?php

declare(strict_types=1);

namespace App\Tests\Twig\Utils;

use App\Twig\Utils\HumanFriendly;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class HumanFriendlyTest extends TestCase
{
    private static HumanFriendly $subject;

    #[Override]
    public static function setUpBeforeClass(): void
    {
        self::$subject = new HumanFriendly();
    }

    #[DataProvider('shortUrlDataProvider')]
    public function testShortUrl(string $input, string $expected): void
    {
        self::assertSame($expected, self::$subject->shortUrl($input));
    }

    /**
     * @return array<array{string, string}>
     */
    public static function shortUrlDataProvider(): array
    {
        return [
            ['http://www.getfursu.it/', 'getfursu.it'],
            ['https://ww.getfursu.it/new', 'ww.getfursu.it/new'],
            ['https://www.furaffinity.net/user/lisoov/', 'furaffinity.net/u/lisoov'],
            ['furaffinity.net/journal/10373912/', 'furaffinity.net/j/10373912'],
            ['https://www.getfursu.it/34567890123456789012345678901234567890+', 'getfursu.it/3456789012345678901234567890...'],
        ];
    }

    #[DataProvider('regexDataProvider')]
    public function testRegex(string $input, string $expected): void
    {
        self::assertSame($expected, self::$subject->regex($input));
    }

    /**
     * @return array<array{string, string}>
     */
    public static function regexDataProvider(): array
    {
        return [
            ['PART(?!IAL)S', 'PARTS'],
            ['(?<![A-Z])LCD', 'LCD'],
            ['(LCD|LED|EL)', 'LCD|LED|EL'],
            ['BASES?|BLANKS?', 'BASES|BLANKS'],
        ];
    }
}
