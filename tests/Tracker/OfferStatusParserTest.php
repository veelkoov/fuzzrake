<?php

declare(strict_types=1);

namespace App\Tests\Tracker;

use App\Tests\TestUtils\DataDefinitions;
use App\Tests\TestUtils\Paths;
use App\Tests\TestUtils\RegexesProviderMock;
use App\Tracker\OfferStatus;
use App\Tracker\OfferStatusParser;
use App\Tracker\PatternProvider;
use App\Tracker\Regexes;
use App\Tracker\RegexFactory;
use App\Tracker\TrackerException;
use App\Utils\Json;
use App\Utils\Web\WebpageSnapshot\Jar;
use App\Utils\Web\WebpageSnapshot\Snapshot;
use Exception;
use PHPUnit\Framework\TestCase;

use function Psl\File\read;

class OfferStatusParserTest extends TestCase
{
    private static OfferStatusParser $csp;

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

        self::$csp = new OfferStatusParser(new PatternProvider(new RegexesProviderMock($regexes)));
    }

    /**
     * @param array<array{0: string, 1: bool}> $expectedResult
     *
     * @dataProvider analyseStatusDataProvider
     *
     * @throws TrackerException
     */
    public function testGetStatuses(string $testSetPath, Snapshot $snapshot, array $expectedResult): void
    {
        $actual = array_map(fn (OfferStatus $offerStatus): string => "{$offerStatus->getOffer()}: ".($offerStatus->getStatus() ? 'OPEN' : 'CLOSED'), self::$csp->getCommissionsStatuses($snapshot));

        $expected = array_map(fn (array $offerStatus): string => "$offerStatus[0]: ".($offerStatus[1] ? 'OPEN' : 'CLOSED'), $expectedResult);

        sort($actual);
        sort($expected);

        $actual = "\n".implode("\n", $actual)."\n";
        $expected = "\n".implode("\n", $expected)."\n";

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function analyseStatusDataProvider(): array // @phpstan-ignore-line
    {
        $paths = array_filter(glob(Paths::getTestDataPath('/statuses/*/*/expected.json')) ?: []);

        return array_map(function ($filepath) {
            $expectedResult = Json::decode(trim(read($filepath)));
            $snapshot = Jar::load(dirname($filepath));

            return [basename(dirname($filepath)), $snapshot, $expectedResult];
        }, $paths);
    }
}
