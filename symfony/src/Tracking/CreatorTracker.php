<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Web\Snapshots\SnapshotsManager;

class CreatorTracker
{
    public function __construct(
        private readonly SnapshotsManager $snapshotsManager,
        private readonly SnapshotProcessor $snapshotProcessor,
        private readonly CreatorUpdater $creatorUpdater,
    ) {
    }

    public function update(Creator $creator, bool $retryPossible): bool
    {
        $analysisResults = $this->getAnalysisResults($creator);

        if ($analysisResults->anySuccess() || !$retryPossible) {
            $this->creatorUpdater->applyResults($creator, $analysisResults);
        }

        return $analysisResults->anySuccess();
    }

    private function getAnalysisResults(Creator $creator): AnalysisResults
    {
        $results = [];

        foreach ($creator->getCommissionsUrlObjects() as $url) {
            $snapshot = $this->snapshotsManager->get($url, true);

            $results[] = $this->snapshotProcessor->analyse($snapshot);
        }

        return AnalysisAggregator::aggregate($results);
    }
}
