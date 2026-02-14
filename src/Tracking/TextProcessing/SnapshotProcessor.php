<?php

declare(strict_types=1);

namespace App\Tracking\TextProcessing;

use App\Tracking\ContextLogger;
use App\Tracking\Data\AnalysisInput;
use App\Tracking\Data\AnalysisResult;
use App\Tracking\Patterns\Patterns;
use App\Utils\Enforce;
use App\Utils\Regexp\RegexUtl;
use Composer\Pcre\Regex;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\Lists\StringList;
use Veelkoov\Debris\Maps\StringToString;

class SnapshotProcessor
{
    private const string GRP_NAME_STATUS_OPEN = 'StatusOpen';
    private const string GRP_NAME_STATUS_CLOSED = 'StatusClosed';

    private readonly ContextLogger $logger;

    public function __construct(
        #[Autowire(service: 'monolog.logger.tracking')]
        LoggerInterface $logger,
        private readonly Patterns $patterns,
        private readonly Preprocessor $preprocessor,
    ) {
        $this->logger = new ContextLogger($logger);
    }

    public function process(AnalysisInput $input): AnalysisResult
    {
        $this->logger->resetContextFor($input);

        if (200 !== $input->snapshot->metadata->httpCode) {
            $this->logger->info('Skipping analysis of non-200 fetched result.');

            return new AnalysisResult($input->url->getUrl(), new StringList(), new StringList(), true);
        }

        $remainingContent = $this->preprocessor->getPreprocessedContent($input);
        $openFor = new StringList();
        $closedFor = new StringList();
        $hasEncounteredIssues = false;

        foreach ($this->patterns->offersStatuses as $pattern) {
            $this->logger->addContext('pattern', $pattern);

            while (null !== ($finding = $this->getOfferStatus($pattern, $remainingContent))) {
                $remainingContent = str_replace_limit($finding->matchedText, ' MATCH ', $remainingContent, 1);

                if (!$finding->isValid()) {
                    $hasEncounteredIssues = true;
                } elseif ($finding->isOpen) {
                    $openFor->addAll($finding->offers);
                } else {
                    $closedFor->addAll($finding->offers);
                }
            }
        }

        return new AnalysisResult($input->url->getUrl(), $openFor->freeze(), $closedFor->freeze(), $hasEncounteredIssues);
    }

    private function getOfferStatus(string $pattern, string $remainingContent): ?OffersFinding
    {
        $match = Regex::match(Enforce::nonEmptyString($pattern), $remainingContent);

        if (!$match->matched) {
            return null;
        }

        $matchedText = $match->matches[0] ?? throw new RuntimeException('Missing matches index 0.');
        $matches = RegexUtl::namedGroups($match->matches);

        $isOpen = $this->getSingleStatusKeyRemove($matches);
        $offers = $this->getOffersFromGroups($matches);

        return new OffersFinding($matchedText, $offers, $isOpen);
    }

    private function getSingleStatusKeyRemove(StringToString $matches): ?bool
    {
        if ($matches->hasKey(self::GRP_NAME_STATUS_OPEN)) {
            $isOpen = true;
        } elseif ($matches->hasKey(self::GRP_NAME_STATUS_CLOSED)) {
            $isOpen = false;
        } else {
            $isOpen = null;

            $this->logger->error('Status group not matched.');
        }

        $matches->removeAllKeys([self::GRP_NAME_STATUS_OPEN, self::GRP_NAME_STATUS_CLOSED]);

        return $isOpen;
    }

    private function getOffersFromGroups(StringToString $matches): StringList
    {
        $result = new StringList();

        foreach ($matches->getKeys() as $key) {
            $result->addAll(GroupNamesTranslator::toOffers($key));
        }

        return $result;
    }
}
