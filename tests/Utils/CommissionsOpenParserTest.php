<?php

namespace App\Tests\Utils;

use App\Utils\CommissionsOpenParser;
use PHPUnit\Framework\TestCase;

class CommissionsOpenParserTest extends TestCase
{

    /**
     * @dataProvider areCommissionsOpenDataProvider
     */
    public function testAreCommissionsOpen($webpageTextFileName, $webpageText, $expectedResult)
    {
        $this->assertEquals($expectedResult, CommissionsOpenParser::areCommissionsOpen($webpageText),
            "Wrong result for '$webpageTextFileName'");
    }

    public function areCommissionsOpenDataProvider()
    {
        return array_map(function ($filepath) {
            if (substr_compare($filepath, '_open.html', -10) === 0) {
                $expectedResult = true;
            } elseif (substr_compare($filepath, '_closed.html', -12) === 0) {
                $expectedResult = false;
            } else {
                throw new \LogicException("Invalid filename: $filepath");
            }

            return [basename($filepath), file_get_contents($filepath), $expectedResult];
        }, glob(__DIR__ . '/../snapshots/*.html'));
    }
}
