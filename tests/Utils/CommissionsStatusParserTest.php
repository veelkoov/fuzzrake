<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Regexp\Utils as Regexp;
use App\Utils\Tracking\CommissionsStatusParser;
use App\Utils\Tracking\TrackerException;
use App\Utils\Web\WebpageSnapshot;
use PHPUnit\Framework\TestCase;

class CommissionsStatusParserTest extends TestCase
{
    const FILENAME_PATTERN = '#^\d+_(?<status>open|closed|unknown)\.json$#';

    /**
     * @var CommissionsStatusParser
     */
    private static $csp;

    public static function setUpBeforeClass()
    {
        self::$csp = new CommissionsStatusParser();
    }

    /**
     * @dataProvider areCommissionsOpenDataProvider
     *
     * @param string          $webpageTextFileName
     * @param WebpageSnapshot $snapshot
     * @param bool|null       $expectedResult
     *
     * @throws TrackerException
     */
    public function testAreCommissionsOpen(string $webpageTextFileName, WebpageSnapshot $snapshot, ?bool $expectedResult)
    {
        try {
            $result = self::$csp->areCommissionsOpen($snapshot);
        } catch (TrackerException $exception) {
            if ('NONE matches' === $exception->getMessage()) {
                $result = null;
            } else {
                throw $exception;
            }
        }

        $this->assertSame($expectedResult, $result, "Wrong result for '$webpageTextFileName'");
    }

    public function areCommissionsOpenDataProvider()
    {
        return array_filter(array_map(function ($filepath) {
            if (!Regexp::match(self::FILENAME_PATTERN, basename($filepath), $matches)) {
                echo "Invalid filename: $filepath\n";

                return false;
            }

            switch ($matches['status']) {
                case 'open':
                    $expectedResult = true;
                    break;
                case 'closed':
                    $expectedResult = false;
                    break;
                default:
                    $expectedResult = null;
            }

            $snapshot = WebpageSnapshot::fromJson(file_get_contents($filepath));

            return [basename($filepath), $snapshot, $expectedResult];
        }, glob(__DIR__.'/../snapshots/**/*.json', GLOB_BRACE)));
    }
}
