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
    public function testAreCommissionsOpen($webpageTextFileName, $webpageText, $expectedResult)
    {
        try {
            $result = self::$csp->areCommissionsOpen($webpageText);
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
        return array_map(function ($filepath) {
            if (substr_compare($filepath, '_open.', -10, 6) === 0) {
                $expectedResult = true;
            } elseif (substr_compare($filepath, '_closed.', -12, 8) === 0) {
                $expectedResult = false;
            } elseif (substr_compare($filepath, '_unknown.', -13, 9) === 0) {
                $expectedResult = null;
            } else {
                throw new \LogicException("Invalid filename: $filepath");
            }

            return [basename($filepath), file_get_contents($filepath), $expectedResult];
        }, glob(__DIR__ . '/../snapshots/*.{html,json}', GLOB_BRACE));
    }
}
