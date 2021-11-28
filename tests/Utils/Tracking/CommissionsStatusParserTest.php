<?php

declare(strict_types=1);

namespace App\Tests\Utils\Tracking;

use App\Utils\Json;
use App\Utils\Tracking\CommissionsStatusParser;
use App\Utils\Tracking\OfferStatus;
use App\Utils\Tracking\Patterns;
use App\Utils\Tracking\TrackerException;
use App\Utils\Web\Snapshot\WebpageSnapshot;
use App\Utils\Web\Snapshot\WebpageSnapshotJar;
use Exception;
use PHPUnit\Framework\TestCase;

class CommissionsStatusParserTest extends TestCase
{
    private static CommissionsStatusParser $csp;

    public static function setUpBeforeClass(): void
    {
        self::$csp = new CommissionsStatusParser(new Patterns());
    }

    /**
     * @dataProvider analyseStatusDataProvider
     *
     * @throws TrackerException
     */
    public function testGetStatuses(string $testSetPath, WebpageSnapshot $snapshot, array $expectedResult): void
    {
        $actual = array_map(function (OfferStatus $offerStatus): string {
            return "{$offerStatus->getOffer()}: ".($offerStatus->getStatus() ? 'OPEN' : 'CLOSED');
        }, self::$csp->getCommissionsStatuses($snapshot));

        $expected = array_map(function (array $offerStatus): string {
            return "$offerStatus[0]: ".($offerStatus[1] ? 'OPEN' : 'CLOSED');
        }, $expectedResult);

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
