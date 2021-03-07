<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Json;
use App\Utils\Tracking\CommissionsStatusParser;
use App\Utils\Tracking\HtmlPreprocessor;
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
        self::$csp = new CommissionsStatusParser(new HtmlPreprocessor());
    }

    /**
     * @dataProvider analyseStatusDataProvider
     * @noinspection PhpUnusedParameterInspection
     */
    public function testGetStatuses(string $testSetPath, WebpageSnapshot $snapshot, array $expectedResult): void
    {
        $statuses = self::$csp->getStatuses($snapshot);

        foreach ($statuses as $status) {
            self::assertContains($status->getOffer(), array_keys($expectedResult), "Detected unwanted status {$status->getOffer()}: [{$status->getIsOpen()}]");

            self::assertEquals($expectedResult[$status->getOffer()], $status->getIsOpen(), "Wrong status detected for {$status->getOffer()}");
            unset($expectedResult[$status->getOffer()]);
        }

        foreach ($expectedResult as $offer => $isOpen) {
            self::fail("Failed detecting status {$offer}: [{$isOpen}]");
        }
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
        }, glob(__DIR__.'/../test_data/statuses/*/expected.json')));
    }

    /**
     * @throws TrackerException
     */
    public function testGuessFilterFromUrl(): void
    {
        self::assertEquals('guessFilter', HtmlPreprocessor::guessFilterFromUrl('AnyKindOfUrl#guessFilter'));
        self::assertEquals('', HtmlPreprocessor::guessFilterFromUrl('AnyKindOfUrl#'));
        self::assertEquals('', HtmlPreprocessor::guessFilterFromUrl('AnyKindOfUrl'));
    }
}
