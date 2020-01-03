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

    public static function setUpBeforeClass(): void
    {
        self::$csp = new CommissionsStatusParser();
    }

    /**
     * @dataProvider areCommissionsOpenDataProvider
     *
     * @throws TrackerException
     */
    public function testAreCommissionsOpen(string $webpageTextFileName, WebpageSnapshot $snapshot, ?bool $expectedResult)
    {
        $result = self::$csp->analyseStatus($snapshot);
        $errorMsg = "Wrong result for '$webpageTextFileName'";

        if (!($cc = $result->getClosedStrContext())->empty()) {
            $errorMsg .= "\nCLOSED: \e[0;30;47m{$cc->getBefore()}\e[0;30;41m{$cc->getSubject()}\e[0;30;47m{$cc->getAfter()}\e[0m";
        }

        if (!($oc = $result->getOpenStrContext())->empty()) {
            $errorMsg .= "\nOPEN: \e[0;30;47m{$oc->getBefore()}\e[0;30;42m{$oc->getSubject()}\e[0;30;47m{$oc->getAfter()}\e[0m";
        }

        static::assertSame($expectedResult, $result->getStatus(), $errorMsg);
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
        }, glob(__DIR__.'/../snapshots/**/*.json')));
    }
}
