<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Patterns\Patterns;
use App\Utils\Collections\StringList;
use App\Utils\Enforce;
use App\Utils\Regexp\RegexUtl;
use App\Utils\Web\Snapshots\Snapshot;
use Composer\Pcre\Regex;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\Maps\StringToString;

class SnapshotProcessor
{
    private ContextLogger $logger;

    public function __construct(
        #[Autowire(service: 'monolog.logger.tracking')]
        LoggerInterface $logger,
        private readonly Patterns $patterns,
    ) {
        $this->logger = new ContextLogger($logger);
    }

    public function analyse(Snapshot $snapshot): AnalysisResult
    {
        $this->logger->clearContext();
        $this->logger->addContext('url', $snapshot->metadata->url);
        $this->logger->addContext('creator', $snapshot->metadata->creatorId);

        $remainingContent = $snapshot->contents;
        $openFor = new StringList();
        $closedFor = new StringList();
        $hasEncounteredIssues = false;

        foreach ($this->patterns->offersStatuses as $pattern) {
            $this->logger->addContext('pattern', $pattern);

            $pattern = Enforce::nonEmptyString($pattern);

            while (null !== ($finding = $this->getOfferStatus($pattern, $remainingContent))) {
                $remainingContent = str_replace_limit($finding->matchedText, ' MATCH ', $remainingContent, 1);

                if (!$finding->isValid()) {
                    $hasEncounteredIssues = true;
                    continue;
                }

                if ($finding->isOpen) {
                    $openFor->add($finding->offer);
                } else {
                    $closedFor->add($finding->offer);
                }
            }
        }

        return new AnalysisResult($snapshot->metadata->url, $openFor->freeze(), $closedFor->freeze(), $hasEncounteredIssues);
    }

    /**
     * @param non-empty-string $pattern
     */
    private function getOfferStatus(string $pattern, string $remainingContent): ?AnalysisFinding
    {
        $match = Regex::match($pattern, $remainingContent);

        if (!$match->matched) {
            return null;
        }

        $matchedText = $match->matches[0] ?? throw new RuntimeException('Missing matches index 0.');
        $matches = RegexUtl::namedGroups($match->matches);

        $isOpen = $this->getSingleStatusKeyRemove($matches);
        $offer = $this->getSingleOfferKey($matches);

        return new AnalysisFinding($matchedText, $offer, $isOpen);
    }

    private function getSingleStatusKeyRemove(StringToString $matches): ?bool
    {
        if ($matches->hasKey('StatusOpen')) {
            $isOpen = true;
        } elseif ($matches->hasKey('StatusClosed')) {
            $isOpen = false;
        } else {
            $isOpen = null;

            $this->logger->error('Status group not matched.');
        }

        $matches->removeAllKeys(['StatusOpen', 'StatusClosed']);

        return $isOpen;
    }

    private function getSingleOfferKey(StringToString $matches): ?string
    {
        if (1 !== $matches->count()) {
            $this->logger->error("Matched {$matches->count()} offer groups.");

            return null;
        }

        return $matches->singleKey(); // FIXME: Pretty name
    }
}
