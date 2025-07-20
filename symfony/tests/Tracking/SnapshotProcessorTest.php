<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tests\TestUtils\DataDefinitions;
use App\Tests\TestUtils\Paths;
use App\Tracking\AnalysisAggregator;
use App\Tracking\Patterns\Patterns;
use App\Tracking\Patterns\RegexesLoader;
use App\Tracking\SnapshotProcessor;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Snapshots\SnapshotMetadata;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Veelkoov\Debris\StringList;

#[Small]
class SnapshotProcessorTest extends FuzzrakeTestCase
{
    private static SnapshotProcessor $processor;
    private static AnalysisAggregator $aggregator;
    private static Creator $creator;

    #[Override]
    public static function setUpBeforeClass(): void
    {
        $patterns = DataDefinitions::get('tracking.yaml', 'tracking');
        $regexesLoader = new RegexesLoader($patterns); // @phpstan-ignore argument.type

        $logger = self::createStub(LoggerInterface::class);

        self::$creator = new Creator()->setCreatorId('TEST001');
        self::$processor = new SnapshotProcessor($logger, new Patterns($regexesLoader));
        self::$aggregator = new AnalysisAggregator($logger);
    }

    #[DataProvider('analyseDataProvider')]
    public function testAnalyse(string $caseName, string $contents, StringList $exptected): void
    {
        $snapshot = new Snapshot($contents, new SnapshotMetadata('', '', UtcClock::now(), 200, [], []));
        $result = self::$aggregator->aggregate(self::$creator, [self::$processor->analyse($snapshot)]);

        $actual = new StringList()
            ->addAll($result->openFor->map(static fn (string $item) => "+$item"))
            ->addAll($result->closedFor->map(static fn (string $item) => "-$item"));

        if ($result->hasEncounteredIssues) {
            $actual->add('ISSUES');
        }

        self::assertSameItems($exptected, $actual, "$caseName open for mismatch.");
    }

    /**
     * @return iterable<array{string, string, StringList, StringList, bool}>
     */
    public static function analyseDataProvider(): iterable
    {
        $joinedTestsData = new Filesystem()->readFile(Paths::getTestDataPath('snapshot_processor_test.txt'));
        $testsData = explode("\n================================================================\n", $joinedTestsData);

        foreach ($testsData as $testData) {
            [$expected, $contents] = explode("\n--------------------------------\n", $testData);

            $expected = explode("\n", trim($expected));
            $caseName = array_shift($expected);

            yield [
                $caseName,
                $contents,
                new StringList($expected),
            ];
        }
    }
}
