<?php

declare(strict_types=1);

namespace App\Tests\Tracker;

use App\Tests\TestUtils\Paths;
use App\Tracker\OfferStatus;
use App\Tracker\OfferStatusParser;
use App\Tracker\PatternFactory;
use App\Tracker\TrackerException;
use App\Utils\Json;
use App\Utils\Web\Snapshot\WebpageSnapshot;
use App\Utils\Web\Snapshot\WebpageSnapshotJar;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class OfferStatusParserTest extends TestCase
{
    private static OfferStatusParser $csp;

    public static function setUpBeforeClass(): void
    {
        $parameters = Yaml::parseFile(Paths::getDataDefinitionsPath('tracker_regexes.yaml'));

        self::$csp = new OfferStatusParser(new PatternFactory($parameters['parameters']['tracker_regexes']));
    }

    /**
     * @dataProvider analyseStatusDataProvider
     *
     * @throws TrackerException
     */
    public function testGetStatuses(string $testSetPath, WebpageSnapshot $snapshot, array $expectedResult): void
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
    public function analyseStatusDataProvider(): array
    {
        return array_filter(array_map(function ($filepath) {
            $expectedResult = Json::decode(trim(file_get_contents($filepath)));
            $snapshot = WebpageSnapshotJar::load(dirname($filepath));

            return [basename(dirname($filepath)), $snapshot, $expectedResult];
        }, glob(__DIR__.'/../../test_data/statuses/*/*/expected.json')));
    }
}
