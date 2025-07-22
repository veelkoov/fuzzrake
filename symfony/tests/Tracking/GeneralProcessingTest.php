<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tests\TestUtils\DataDefinitions;
use App\Tests\TestUtils\Paths;
use App\Tracking\AnalysisAggregator;
use App\Tracking\Patterns\Patterns;
use App\Tracking\Patterns\RegexesLoader;
use App\Tracking\TextProcessing\Preprocessor;
use App\Tracking\TextProcessing\SnapshotProcessor;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Veelkoov\Debris\StringList;

#[Small]
class GeneralProcessingTest extends FuzzrakeTestCase
{
    private static SnapshotProcessor $processor;
    private static AnalysisAggregator $aggregator;
    private static Creator $creator;

    #[Override]
    public static function setUpBeforeClass(): void
    {
        $patternsData = DataDefinitions::get('tracking.yaml', 'tracking');
        $regexesLoader = new RegexesLoader($patternsData); // @phpstan-ignore argument.type
        $patterns = new Patterns($regexesLoader);

        $logger = self::createStub(LoggerInterface::class);

        self::$creator = new Creator()->setCreatorId('TEST001');
        self::$processor = new SnapshotProcessor($logger, $patterns, new Preprocessor($logger, $patterns));
        self::$aggregator = new AnalysisAggregator($logger);
    }

    #[DataProvider('analyseDataProvider')]
    public function testAnalyse(string $caseName, string $contents, StringList $expected): void
    {
        $result = self::$aggregator->aggregate(self::$creator,
            [self::$processor->analyse(self::getAnalysisInput(contents: $contents))]);

        $actual = new StringList()
            ->addAll($result->openFor->map(static fn (string $item) => "+$item"))
            ->addAll($result->closedFor->map(static fn (string $item) => "-$item"));

        if ($result->hasEncounteredIssues) {
            $actual->add('ISSUES');
        }

        self::assertSameItems($expected, $actual, "$caseName open for mismatch.");
    }

    /**
     * @return iterable<array{string, string, StringList}>
     */
    public static function analyseDataProvider(): iterable
    {
        $joinedTestsData = new Filesystem()->readFile(Paths::getTestDataPath('general_processing_test.txt'));
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
