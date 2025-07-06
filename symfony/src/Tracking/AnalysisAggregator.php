<?php

declare(strict_types=1);

namespace App\Tracking;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\StringList;
use Veelkoov\Debris\StringSet;

class AnalysisAggregator
{
    public function __construct(
        /* @phpstan-ignore property.onlyWritten (TODO: Use) */
        #[Autowire(service: 'monolog.logger.tracking')]
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param list<AnalysisResult> $results
     */
    public function aggregate(array $results): AnalysisResults // FIXME
    {
        $openFor = new StringSet();
        $closedFor = new StringSet();
        $hasEncounteredIssues = false;

        foreach ($results as $result) {
            $openFor->addAll($result->openFor);
            $closedFor->addAll($result->closedFor);
            $hasEncounteredIssues = $hasEncounteredIssues || $result->hasEncounteredIssues;
        }

        return new AnalysisResults(new StringList($openFor), new StringList($closedFor), $hasEncounteredIssues);
    }
}
