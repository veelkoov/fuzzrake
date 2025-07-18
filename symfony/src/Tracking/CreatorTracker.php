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
        $this->logger->info('Trying to update statuses.', [
            'creator' => $creator->getLastCreatorId(),
            'retryPossible' => $retryPossible,
            'refetchPages' => $refetchPages,
        ]);

        $analysisResults = $this->getAnalysisResults($creator, $refetchPages);

        if ($analysisResults->anySuccess() || !$retryPossible) {
            $this->logger->info('Saving statuses update results.', ['creator' => $creator->getLastCreatorId()]);

            $this->creatorUpdater->applyResults($creator, $analysisResults);
        }

        return $analysisResults->anySuccess();
    }

    private function getAnalysisResults(Creator $creator, bool $refetchPages): AnalysisResults
    {
        $results = [];

        foreach ($creator->getCommissionsUrlObjects() as $url) {
            $this->logger->info('Retrieving and analysing.',
                ['creator' => $creator->getLastCreatorId(), 'url' => $url->getUrl()]);

            $snapshot = $this->snapshotsManager->get($url, $refetchPages);

            $results[] = $this->snapshotProcessor->analyse($snapshot);
        }

        $this->logger->info('Aggregating '.count($results).'results.', ['creator' => $creator->getLastCreatorId()]);

        return $this->analysisAggregator->aggregate($results);
    }
}
