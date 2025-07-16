<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Patterns\Patterns;
use App\Utils\Collections\StringList;
use App\Utils\Regexp\RegexUtl;
use App\Utils\Web\Snapshots\Snapshot;
use Composer\Pcre\Regex;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\Maps\StringToString;

class SnapshotProcessor
{
    private string $currentCreatorId = '';
    private string $currentUrl = '';
    private string $currentPattern = '';

    public function __construct(
        #[Autowire(service: 'monolog.logger.tracking')]
        private readonly LoggerInterface $logger,
        private readonly Patterns $patterns,
    ) {
    }

    public function analyse(Snapshot $snapshot): AnalysisResult
    {
        $this->currentCreatorId = 'TODO'; // TODO: $snapshot->metadata->creatorId;

        $remainingContent = $snapshot->contents;
        $openFor = new StringList();
        $closedFor = new StringList();
        $hasEncounteredIssues = false;

        foreach ($this->patterns->offersStatuses as $pattern) {
            $this->currentPattern = $pattern;

            while (null !== ($finding = $this->getOfferStatus($remainingContent))) {
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

        return new AnalysisResult($this->currentUrl, $openFor->freeze(), $closedFor->freeze(), $hasEncounteredIssues);
    }

    private function getOfferStatus(string $remainingContent): ?AnalysisFinding
    {
        /* @phpstan-ignore composerPcre.maybeUnsafeStrictGroups (false-positive) */
        $match = Regex::matchStrictGroups($this->currentPattern, $remainingContent); // @phpstan-ignore argument.type (FIXME)

        if (!$match->matched) {
            return null;
        }

        $matchedText = $match->matches[0];
        $matches = RegexUtl::namedGroups($match->matches);

        $isOpen = $this->getSingleStatusKeyRemove($matches);
        $offer = $this->getSingleOfferKey($matches);

        return new AnalysisFinding($matchedText, $offer, $isOpen);
    }

    private function getSingleStatusKeyRemove(StringToString $matches): ?bool
    {
        if ($matches->hasKey('open')) {
            $isOpen = true;
        } elseif ($matches->hasKey('closed')) {
            $isOpen = false;
        } else {
            $isOpen = null;

            $this->error('Status group not matched.');
        }

        $matches->removeAllKeys(['open', 'closed']);

        return $isOpen;
    }

    private function getSingleOfferKey(StringToString $matches): ?string
    {
        if (1 !== $matches->count()) {
            $this->error("Matched {$matches->count()} offer groups.");

            return null;
        }

        return $matches->singleKey(); // FIXME: Pretty name
    }

    private function error(string $message): void
    {
        $this->logger->error($message, [
            'creatorId' => $this->currentCreatorId,
            'url' => $this->currentUrl,
            'pattern' => $this->currentPattern,
        ]);
    }
}
