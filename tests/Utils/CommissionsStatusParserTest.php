<?php

namespace App\Tests\Utils;

use App\Utils\CommissionsStatusParser;
use App\Utils\CommissionsStatusParserException;
use PHPUnit\Framework\TestCase;

class CommissionsStatusParserTest extends TestCase
{
    /**
     * @var CommissionsStatusParser
     */
    private static $csp;

    public static function setUpBeforeClass() {
        self::$csp = new CommissionsStatusParser();
    }

    /**
     * @dataProvider areCommissionsOpenDataProvider
     */
    public function testAreCommissionsOpen($webpageTextFileName, $webpageText, $expectedResult, $additionalFilter)
    {
        try {
            $result = self::$csp->areCommissionsOpen($webpageText, $additionalFilter);
        } catch (CommissionsStatusParserException $exception) {
            if ($exception->getMessage() === 'NONE matches') {
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
            if (!preg_match('#^\d+_(?<status>open|closed|unknown)(?:_filter_(?<filter>[a-z]+))?\.(html|json)$#',
                basename($filepath), $zapałki)) {
                echo "Invalid filename: $filepath\n";
                return false;
            }

            $additionalFilter = $zapałki['filter'];
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

            return [basename($filepath), file_get_contents($filepath), $expectedResult, $additionalFilter];
        }, glob(__DIR__ . '/../snapshots/*.{html,json}', GLOB_BRACE)));
    }
}
