<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Data\AnalysisResult;
use App\Tracking\Data\AnalysisResults;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\Lists\StringList;
use Veelkoov\Debris\Sets\StringSet;

class AnalysisAggregator
{
    private readonly ContextLogger $logger;

    public function __construct(
        #[Autowire(service: 'monolog.logger.tracking')]
        LoggerInterface $logger,
    ) {
        $this->logger = new ContextLogger($logger);
    }

    /**
     * @param list<AnalysisResult> $results
     */
    public function aggregate(Creator $creator, array $results): AnalysisResults
    {
        $this->logger->addContext('creator', $creator->getLastCreatorId());

        $openFor = new StringSet();
        $closedFor = new StringSet();
        $hasEncounteredIssues = false;

        foreach ($results as &$result) { // & to avoid logging the local duplicates/conflicts later
            $this->logger->addContext('url', $result->url);
            $result = $this->normalizeResult($result);

            $openFor->addAll($result->openFor);
            $closedFor->addAll($result->closedFor);
            $hasEncounteredIssues = $hasEncounteredIssues || $result->hasEncounteredIssues;
        }

        $this->logger->addContext('creator', $creator->getLastCreatorId(), true);

        $contradicting = $openFor->intersect($closedFor);

        if ($contradicting->isNotEmpty()) {
            $resultsAsString = StringList::mapFrom($results, static fn (AnalysisResult $result) => (string) $result)
                ->join(' / ');
            $this->logger->info("Contradicting offers detected: $resultsAsString.");

            $openFor = $openFor->removeAll($contradicting);
            $closedFor = $closedFor->removeAll($contradicting);
            $hasEncounteredIssues = true;
        }

        return new AnalysisResults(new StringList($openFor), new StringList($closedFor), $hasEncounteredIssues);
    }

    private function normalizeResult(AnalysisResult $result): AnalysisResult
    {
        if ($result->openFor->isEmpty() && $result->closedFor->isEmpty()) {
            $this->logger->info("Nothing detected: $result.");

            return $result->with(hasEncounteredIssues: true);
        }

        $uniqueOpenFor = $result->openFor->unique();
        $uniqueClosedFor = $result->closedFor->unique();

        if ($uniqueOpenFor->count() !== $result->openFor->count()
            || $uniqueClosedFor->count() !== $result->closedFor->count()) {
            $this->logger->warning("Duplicated offers detected: $result.");

            $result = $result->with(openFor: $uniqueOpenFor, closedFor: $uniqueClosedFor, hasEncounteredIssues: true);
        }

        $contradicting = $result->openFor->intersect($result->closedFor);

        if ($contradicting->isNotEmpty()) {
            $this->logger->warning("Contradicting offers detected: $result.");

            $result = $result->with(
                openFor: $result->openFor->minusAll($contradicting),
                closedFor: $result->closedFor->minusAll($contradicting),
                hasEncounteredIssues: true,
            );
        }

        return $result;
    }
}
