<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Tests\TestUtils\DataDefinitions;
use App\Tests\TestUtils\Paths;
use App\Tests\TestUtils\RegexesProviderMock;
use App\Tracking\Exception\TrackerException;
use App\Tracking\OfferStatus\OfferStatus;
use App\Tracking\Regex\PatternProvider;
use App\Tracking\Regex\Regexes;
use App\Tracking\Regex\RegexFactory;
use App\Tracking\TextParser;
use App\Tracking\Web\WebpageSnapshot\Jar;
use App\Tracking\Web\WebpageSnapshot\Snapshot;
use App\Utils\DateTime\UtcClock;
use App\Utils\Json;
use Exception;
use PHPUnit\Framework\TestCase;

use function Psl\File\read;
use function Psl\Str\Byte\strip_suffix;
use function Psl\Str\strip_prefix;
use function Psl\Vec\map;

/**
 * @small
 */
class TextParserTest extends TestCase
{
    /**
     * @var list<array{string, array<array{string, bool}>}>
     */
    private static array $CASES = [
        [<<<'CONTENTS'
            Commissions are currently closed. Next opening: winter.
            Quotes are always open!
        CONTENTS, [
            ['COMMISSIONS', false],
            ['QUOTES', true],
        ]],
        [<<<'CONTENTS'
            <div><h1><span>&nbsp;Commission Status:</span></h1></div><div><p><span>OPEN!!</span></p></div>
        CONTENTS, [
            ['COMMISSIONS', true],
        ]],
        [<<<'CONTENTS'
            Unfortunately I am not currently open for custom costume commissions, but feel free to check out what I offer and my base prices on the <span><a href="https://localhost/" target="_self">FURSUIT PRICES</a></span> tab!
        CONTENTS, [
            ['COMMISSIONS', false],
        ]],
        [<<<'CONTENTS'
            <p class="font_8" style="font-size:25px; line-height:1.5em; text-align:center"><span style="font-family:libre baskerville,serif"><span class="color_25"><span style="font-size:25px"><span style="letter-spacing:0.03em">We are currently CLOSED for new projects.</span></span></span></span></p>
        CONTENTS, [
            ['PROJECTS', false],
        ]],
        [<<<'CONTENTS'
            <p align="center"><span style="color: rgb(255, 102, 0); font-size: 36px;"><span style="color: rgb(255, 102, 0); font-size: 36px;"><span style="color: rgb(255, 102, 0);" class="wz-bold"><span style="color: rgb(255, 102, 0);" class="wz-bold">WE ARE CURRENTLY TAKING COMMISSIONS</span></span></span><br style="color: rgb(255, 102, 0); font-size: 36px;"></span></p>
        CONTENTS, [
            ['COMMISSIONS', true],
        ]],
        [<<<'CONTENTS'
            <!-- end .sidebar1 --></div>

            <!--<div class="status">
                <span style="color: #FFF; font-size: 18px;">We are currently</span>
                <span style="color: #9F0; font-size: 24px;">OPEN</span>
            <span style="color: #FFF; font-size: 18px;">for commissions!</span></div>-->

            <div class="status">
                    <span style="color: #FFF; font-size: 18px;">We are currently</span>
                    <span style="color: #F90; font-size: 24px;">CLOSED</span>
                <span style="color: #FFF; font-size: 18px;">for commissions!</span></div>

            <!-- InstanceBeginEditable name="Page Content" -->
        CONTENTS, [
            ['COMMISSIONS', false],
        ]],
        [<<<'CONTENTS'
            <br />
            Fursuit commission are currently &gt;&gt;open&lt;&lt; <br />
            <br />
        CONTENTS, [
            ['COMMISSIONS', true],
        ]],
        [<<<'CONTENTS'
            <div id="comp-jkk2impo" class="_2bafp" data-testid="richTextElement"><h1 class="font_0" style="text-align:center;font-size:75px">CLOSED</h1></div><div id="comp-jkk2u1hx" class="_2bafp" data-testid="richTextElement"><h2 class="font_2" style="font-size:20px; text-align:center"><span style="font-size:20px">Currently not accepting new projects</span><br />
            <span style="font-size:20px">Next opening: TBA</span></h2></div>
        CONTENTS, [
            ['PROJECTS', false],
        ]],
        [<<<'CONTENTS'
            <div id="comp-jrlkane6" class="BaOVQ8 tz5f0K comp-jrlkane6 wixui-text" data-testid="richTextElement"><p class="font_7" style="font-size:22px; text-align:center;"><span style="font-size:22px;"><span style="font-family:bree-w01-thin-oblique,sans-serif;"><span style="color:#FFFFFF;"><span style="text-decoration:underline;"><span style="font-weight:bold;">Commission Status:</span></span></span></span></span></p>

            <p class="font_7" style="font-size:22px; text-align:center;"><span style="font-size:22px;"><span class="color_23"><span style="font-weight:bold;"><span style="font-family:amatic sc,cursive;"><span style="background-color:#FFFFFF;">Currently Closed</span></span></span></span></span></p></div>
        CONTENTS, [
            ['COMMISSIONS', false],
        ]],
        [<<<'CONTENTS'
            <br />
            <span class="bbcode" style="color: orange;"><strong class="bbcode bbcode_b">COMMISSIONS STATUS:</span><br />
            <span class="bbcode" style="color: RED;">CLOSED</span> <br />
            <br />
        CONTENTS, [
            ['COMMISSIONS', false],
        ]],
        [<<<'CONTENTS'
            <code class="bbcode bbcode_center"><strong class="bbcode bbcode_b"><span class="bbcode" style="color: orange;">Commission status:</span><span class="bbcode" style="color: red;">Closed - next opening will be 3rd of September 2021</span></strong></code> <br />
        CONTENTS, [
            ['COMMISSIONS', false],
        ]],
        [<<<'CONTENTS'
            <div class='widget-content'>
            <center><b><span =""  style="color:white;">Fursuit Commissions and </span></b><b =""  style="font-size:100%;"><span =""  style="color:white;">Quote</span></b><b  style="text-align: left;font-size:100%;"><span =""  style="color:white;">s :</span><span =""  style="color:#ff99ff;"> CLOSED </span></b></center><center><br /></center>
            </div>
        CONTENTS, [
            ["COMMISSIONS", false],
            ["QUOTES", false]
        ]],
        [<<<'CONTENTS'
            ✎ STATUS ✎<br />
            [ Quotes. . . . Open ]<br />
            [ Commissions. .Closed ]<br />
        CONTENTS, [
            ["COMMISSIONS", false],
            ["QUOTES", true]
        ]],
        [<<<'CONTENTS'
            <div class="row">
                    <div class="col">
                <h2>COMMISSIONS ARE CURRENTLY 
                        CLOSED		    </h2>
                <p>Re-opening: TBA</p>                </div>
                </div>
        CONTENTS, [
            ["COMMISSIONS", false],
        ]],
        [<<<'CONTENTS'
            Commissions/Quotes Currently Closed Next Opening TBD
        CONTENTS, [
            ["COMMISSIONS", false],
            ["QUOTES", false],
        ]],
        [<<<'CONTENTS'
            <div class="paragraph" style="text-align:center;"><font color="#d5d5d5">Custom animal costumes made to order. Built to almost any design. Prices found below but for an exact price, send in a quote. All prices in USD</font><strong><font color="#818181">.<br /><br />Commissions are currently: CLOSED<br />Quotes always open</font></strong><br /></div>
        CONTENTS, [
            ["COMMISSIONS", false],
            ["QUOTES", true],
        ]],
        [<<<'CONTENTS'
            <p><centre>
            TheStudioName currently is not accepting new commissions.
            <p>
             I will be opening again in September to fill spots for January-March 2022 completion.   THANK YOU!!!
            <p> </p>
        CONTENTS, [
            ["COMMISSIONS", false],
        ]],
    ];

    private static TextParser $csp;

    public static function setUpBeforeClass(): void
    {
        $trackerRegexes = DataDefinitions::get('tracker_regexes.yaml', 'tracker_regexes');
        $factory = new RegexFactory($trackerRegexes); // @phpstan-ignore-line - Data structures

        $regexes = new Regexes(
            $factory->getFalsePositives(),
            $factory->getOfferStatuses(),
            $factory->getGroupsTranslations(),
            $factory->getCleaners(),
        );

        self::$csp = new TextParser(new PatternProvider(new RegexesProviderMock($regexes)));
    }

    /**
     * @param array<array{string, bool}> $expectedResult
     *
     * @dataProvider analyseStatusDataProvider
     *
     * @throws TrackerException
     */
    public function testGetStatuses(string $testSetPath, Snapshot $snapshot, array $expectedResult): void
    {
        $actual = map(self::$csp->getOfferStatuses($snapshot), fn (OfferStatus $offerStatus): string => "{$offerStatus->offer}: ".($offerStatus->status ? 'OPEN' : 'CLOSED'));

        $expected = map($expectedResult, fn (array $offerStatus): string => "$offerStatus[0]: ".($offerStatus[1] ? 'OPEN' : 'CLOSED'));

        sort($actual);
        sort($expected);

        $actual = "\n".implode("\n", $actual)."\n";
        $expected = "\n".implode("\n", $expected)."\n";

        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<string, array{string, Snapshot, array<array{string, bool}>}>
     *
     * @throws Exception
     */
    public function analyseStatusDataProvider(): iterable
    {
        $paths = array_filter(glob(Paths::getTestDataPath('/statuses/*/*/expected.json')) ?: []);
        $prefix = Paths::getTestDataPath('/statuses/');

        foreach ($paths as $path) {
            /**
             * @var array<array{0: string, 1: bool}> $expectedResult
             */
            $expectedResult = Json::decode(read($path));
            $snapshot = Jar::load(dirname($path));
            $case = strip_prefix(strip_suffix($path, '/expected.json'), $prefix);

            yield $case => [basename(dirname($path)), $snapshot, $expectedResult];
        }

        $now = UtcClock::now();

        foreach (self::$CASES as $index => $case) {
            $snapshot = new Snapshot($case[0], '', $now, 'TheStudioName', 200, [], []);

            yield "CASE_$index" => ["CASE_$index", $snapshot, $case[1]];
        }
    }
}
