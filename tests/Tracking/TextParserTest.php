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
    private static TextParser $csp;

    public static function setUpBeforeClass(): void
    {
        $trackerRegexes = DataDefinitions::get('tracker_regexes.yaml', 'tracker_regexes');
        $factory = new RegexFactory($trackerRegexes);

        $regexes = new Regexes(
            $factory->getFalsePositives(),
            $factory->getOfferStatuses(),
            $factory->getGroupTranslations(),
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
    }
}
