<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tests\TestUtils\DataDefinitions;
use App\Tracking\Patterns\Patterns;
use App\Tracking\Patterns\RegexesLoader;
use App\Tracking\Preprocessor;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use Veelkoov\Debris\StringList;

#[Small]
class PreprocessorTest extends FuzzrakeTestCase
{
    private Preprocessor $subject;

    #[Override]
    public function setUp(): void
    {
        /* @phpstan-ignore argument.type */
        $regexesLoader = new RegexesLoader(DataDefinitions::get('tracking.yaml', 'tracking'));
        $this->subject = new Preprocessor(new Patterns($regexesLoader));
    }

    /**
     * @return list<array{string, string}>
     */
    public static function cleanerRegexesAreWorkingDataProvider(): array
    {
        return [
            ['***open***', 'open'],
            ['!closed!', 'closed'],
            [' ❗&nbsp;', ' ! '], // Unicode NBSP, emoticon !, HTML entity NBSP
            ["\t", ' '],
        ];
    }

    #[DataProvider('cleanerRegexesAreWorkingDataProvider')]
    public function testCleanerRegexesAreWorking(string $input, string $expected): void
    {
        $result = $this->subject->preprocess($input, new StringList());

        self::assertSame($expected, $result);
    }

    public function testInputGetsConvertedToLowercase(): void
    {
        $result = $this->subject->preprocess('AaBbCcDdEeFf', new StringList());

        self::assertSame('aabbccddeeff', $result);
    }

    /**
     * @return list<array{string, StringList, string}>
     */
    public static function creatorAliasesAreGettingReplacedWithTheNamePlaceholderDataProvider(): array
    {
        return [
            [
                'An Intergalactic House of Pancakes work',
                new StringList(['Intergalactic House of Pancakes']),
                'an CREATOR_NAME work',
            ],
            [
                "An Intergalactic House of Pancake's work",
                new StringList(['Intergalactic House of Pancakes']),
                'an CREATOR_NAME work',
            ],
            [
                "About Intergalactic Pancake's work",
                new StringList(['Intergalactic Pancake']),
                "about CREATOR_NAME's work",
            ],
            [
                // Multiple aliases, 's form, case-insensitive, "creator" in aliases
                "asdf Studio's uiop Creator asdf Studios zxcv",
                new StringList(['StUdIoS', 'cReatOR']),
                'asdf CREATOR_NAME uiop CREATOR_NAME asdf CREATOR_NAME zxcv',
            ],
        ];
    }

    #[DataProvider('creatorAliasesAreGettingReplacedWithTheNamePlaceholderDataProvider')]
    public function testAliasesGetReplacedWithPlaceholder(string $input, StringList $aliases, string $expected): void
    {
        $result = $this->subject->preprocess($input, new StringList($aliases));

        self::assertSame($expected, $result);
    }

    /**
     * @return list<array{string, string}>
     */
    public static function falsePositivesAreBeingRemovedDataProvider(): array
    {
        return [
            ["even though you're closed for commissions", 'FALSE_POSITIVE'],
            ['while mine commissions are open', 'FALSE_POSITIVE'],
            ['if my quotes open', 'FALSE_POSITIVE'],
            ['- art commissions are open', '- FALSE_POSITIVE are open'],
            ['after the commissions close', 'FALSE_POSITIVE'],
            ['although comms are closed', 'FALSE_POSITIVE'],
            ["as soon as we're open", 'FALSE_POSITIVE'],
            ['next commissions opening', 'FALSE_POSITIVE'],
            ['commissions: open January', 'FALSE_POSITIVE'],
            ['The Creator is now opening for quotes a few weeks before commission slots open', 'FALSE_POSITIVE'],
            ['when do you open for', 'FALSE_POSITIVE'],
            ["when i'm taking", 'FALSE_POSITIVE'],
            ['when will you start taking new commissions?', 'FALSE_POSITIVE?'],
        ];
    }

    #[DataProvider('falsePositivesAreBeingRemovedDataProvider')]
    public function testFalsePositivesAreBeingRemoved(string $input, string $expected): void
    {
        $result = $this->subject->preprocess($input, StringList::of('The Creator'));

        self::assertSame($expected, $result);
    }
}
