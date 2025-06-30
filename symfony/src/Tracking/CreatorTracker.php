<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Web\Snapshots\SnapshotsManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CreatorTracker
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.tracking')]
        private readonly LoggerInterface $logger,
        private readonly SnapshotsManager $snapshotsManager,
        private readonly SnapshotProcessor $snapshotProcessor,
        private readonly AnalysisAggregator $analysisAggregator,
        private readonly CreatorUpdater $creatorUpdater,
    ) {
    }

    public function update(Creator $creator, bool $retryPossible, bool $refetchPages): bool
    {
        $analysisResults = $this->getAnalysisResults($creator, $refetchPages);

        if ($analysisResults->anySuccess() || !$retryPossible) {
            $this->creatorUpdater->applyResults($creator, $analysisResults);
        }

        return $analysisResults->anySuccess();
    }

    private function getAnalysisResults(Creator $creator, bool $refetchPages): AnalysisResults
    {
        $results = [];

        foreach ($creator->getCommissionsUrlObjects() as $url) {
            $snapshot = $this->snapshotsManager->get($url, $refetchPages);

            $results[] = $this->snapshotProcessor->analyse($snapshot);
        }

        return $this->analysisAggregator->aggregate($results);
    }
}
