<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\DateTime\DateTimeException;
use App\Utils\Tracking\CommissionsStatusParser;
use App\Utils\Tracking\TrackerException;
use App\Utils\Web\Snapshot\WebpageSnapshot;
use App\Utils\Web\Snapshot\WebpageSnapshotJar;
use JsonException;
use PHPUnit\Framework\TestCase;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;

class CommissionsStatusParserTest extends TestCase
{
    private static CommissionsStatusParser $csp;

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

    /**
     * @throws DateTimeException|JsonException|NonexistentGroupException
     */
    public function areCommissionsOpenDataProvider(): array
    {
        $pattern = pattern('/\d+_(?<status>open|closed|unknown)/metadata\.json$');

        return array_filter(array_map(fn (string $filepath): array => $pattern->match($filepath)
            ->findFirst(function (Detail $detail) use ($filepath): array {
                $expectedResult = match ($detail->get('status')) {
                    'open'   => true,
                    'closed' => false,
                    default  => null,
                };

                $snapshot = WebpageSnapshotJar::load(dirname($filepath));

                return [basename(dirname($filepath)), $snapshot, $expectedResult];
            })->orThrow(), glob(__DIR__.'/../snapshots/*/*/metadata.json')));
    }
}
