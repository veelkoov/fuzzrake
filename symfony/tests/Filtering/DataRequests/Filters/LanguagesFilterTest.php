<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests\Filters;

use App\Filtering\DataRequests\Filters\LanguagesFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @small
 */
class LanguagesFilterTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     *
     * @param list<string> $languages
     * @param list<string> $searched
     */
    public function testMatches(array $languages, array $searched, bool $matched): void
    {
        $subject = new LanguagesFilter($searched);
        $artisan = Artisan::new()->setLanguages($languages);

        self::assertEquals($matched, $subject->matches($artisan));
    }

    public function matchesProvider(): DataProvider
    {
        return DataProvider::tuples( // languages, searched, matched
            [[],                               [],                             false],
            [[],                               ['Finnish'],                    false],
            [['Finnish', 'Finnish (limited)'], [],                             false],
            [['Finnish', 'Finnish (limited)'], ['Finnish'],                    true],
            [['Finnish', 'Finnish (limited)'], ['Finnish (limited)'],          true],
            [['Finnish', 'Finnish (limited)'], ['Czech', 'Finnish (limited)'], true],
            [['Finnish (limited)'],            ['Finnish'],                    false],

            [[],                               ['?'],                               true],
            [['Finnish', 'Finnish (limited)'], ['?'],                               false],
            [['Finnish', 'Finnish (limited)'], ['?', 'Finnish'],                    true],
            [['Finnish', 'Finnish (limited)'], ['?', 'Finnish (limited)'],          true],
            [['Finnish', 'Finnish (limited)'], ['?', 'Czech', 'Finnish (limited)'], true],
            [['Finnish (limited)'],            ['?', 'Finnish'],                    false],
        );
    }
}
