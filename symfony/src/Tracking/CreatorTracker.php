<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Entity\CreatorUrl;
use App\Tracking\Data\AnalysisInput;
use App\Tracking\Data\AnalysisResults;
use App\Tracking\TextProcessing\SnapshotProcessor;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Web\Snapshots\SnapshotsManager;
use App\Utils\Web\Url\Url;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CreatorTracker
{
    private readonly ContextLogger $logger;

    public function __construct(
        #[Autowire(service: 'monolog.logger.tracking')]
        LoggerInterface $logger,
        private readonly SnapshotsManager $snapshotsManager,
        private readonly SnapshotProcessor $snapshotProcessor,
        private readonly AnalysisAggregator $analysisAggregator,
        private readonly CreatorUpdater $creatorUpdater,
    ) {
        $this->logger = new ContextLogger($logger);
    }

    public function update(Creator $creator, bool $retryPossible, bool $refetchPages): bool
    {
        $this->logger->resetContextFor($creator);

        if (!$creator->isTracked()) {
            $this->logger->info('Not tracked. Clearing tracking state.');

            $creator->setOpenFor([]);
            $creator->setClosedFor([]);
            $creator->setCsTrackerIssue(false);
            $creator->setCsLastCheck(null);
        }

        $this->logger->info('Trying to update statuses.', [
            'retryPossible' => $retryPossible,
            'refetchPages' => $refetchPages,
        ]);

        $analysisResults = $this->getAnalysisResults($creator, $refetchPages);

        if ($analysisResults->anySuccess() || !$retryPossible) {
            $this->logger->info('Saving statuses update results.');

            $this->creatorUpdater->applyResults($creator, $analysisResults);

            // TODO: Clear cache
        }

        return $analysisResults->anySuccess();
    }

    private function getAnalysisResults(Creator $creator, bool $refetchPages): AnalysisResults
    {
        $results = [];

        foreach ($creator->getCommissionsUrlObjects() as $url) {
            $trackedUrl = $this->getTrackedUrl($url);

            $this->logger->info('Retrieving and analysing a web page.', ['url' => $trackedUrl->getUrl()]);

            $snapshot = $this->snapshotsManager->get($trackedUrl, $refetchPages);

            $results[] = $this->snapshotProcessor->analyse(new AnalysisInput($trackedUrl, $snapshot, $creator));
        }

        $this->logger->info('Aggregating '.count($results).' results.');

        return $this->analysisAggregator->aggregate($creator, $results);
    }

    private function getTrackedUrl(CreatorUrl $url): Url
    {
        $trackedUrl = $url->getStrategy()->getUrlForTracking($url);

        if ($trackedUrl->getUrl() !== $url->getUrl()) {
            $this->logger->info("Will analyse '{$trackedUrl->getUrl()}' instead of '{$url->getUrl()}'.");
        }

        return $trackedUrl;
    }
}
