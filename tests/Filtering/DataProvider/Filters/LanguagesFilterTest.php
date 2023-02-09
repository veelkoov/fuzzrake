<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataProvider\Filters;

use App\Filtering\DataRequests\Filters\LanguagesFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class LanguagesFilterTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     *
     * @param list<string> $searched
     */
    public function testMatches(string $languages, array $searched, bool $matched): void
    {
        $subject = new LanguagesFilter($searched);
        $artisan = Artisan::new()->setLanguages($languages);

        self::assertEquals($matched, $subject->matches($artisan));
    }

    /**
     * @return list<array{string, list<string>, bool}>
     */
    public function matchesProvider(): array
    {
        return [ // languages, searched, matched
            ['',                           [],                             false],
            ["Finnish\nFinnish (limited)", [],                             false],
            ["Finnish\nFinnish (limited)", ['Finnish'],                    true],
            ["Finnish\nFinnish (limited)", ['Finnish (limited)'],          true],
            ["Finnish\nFinnish (limited)", ['Czech', 'Finnish (limited)'], true],
            ['Finnish (limited)',          ['Finnish'],                    false],

            ['',                           ['?'],                               true],
            ["Finnish\nFinnish (limited)", ['?'],                               false],
            ["Finnish\nFinnish (limited)", ['?', 'Finnish'],                    true],
            ["Finnish\nFinnish (limited)", ['?', 'Finnish (limited)'],          true],
            ["Finnish\nFinnish (limited)", ['?', 'Czech', 'Finnish (limited)'], true],
            ['Finnish (limited)',          ['?', 'Finnish'],                    false],
        ];
    }
}
