<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\CommissionsStatusParser;
use App\Utils\CommissionsStatusParserException;
use PHPUnit\Framework\TestCase;

class CommissionsStatusParserTest extends TestCase
{
    const FILENAME_PATTERN = '#^\d+_(?<status>open|closed|unknown)(?:_filter_(?<filter>[a-z]+))?(?:_name_(?<name>[a-zA-Z+]+))?\.(html|json)$#';

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
     */
    public function testAreCommissionsOpen(string $webpageTextFileName, string $webpageText, ?bool $expectedResult,
                                           string $additionalFilter, string $studioName)
    {
        try {
            $result = self::$csp->areCommissionsOpen($webpageText, $studioName, $additionalFilter);
        } catch (CommissionsStatusParserException $exception) {
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
            if (!preg_match(self::FILENAME_PATTERN,
                basename($filepath), $zapałki)) {
                echo "Invalid filename: $filepath\n";

                return false;
            }

            $additionalFilter = $zapałki['filter'];
            $studioName = urldecode($zapałki['name']);
            switch ($zapałki['status']) {
                case 'open':
                    $expectedResult = true;
                    break;
                case 'closed':
                    $expectedResult = false;
                    break;
                default:
                    $expectedResult = null;
            }

            return [basename($filepath), file_get_contents($filepath), $expectedResult, $additionalFilter, $studioName];
        }, glob(__DIR__.'/../snapshots/**/*.{html,json}', GLOB_BRACE)));
    }
}
