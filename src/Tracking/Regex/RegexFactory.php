<?php

declare(strict_types=1);

namespace App\Tracking\Regex;

use App\Tracking\Exception\ConfigurationException;
use TRegx\CleanRegex\Pattern;

class RegexFactory
{
    /**
     * @var list<string>
     */
    private array $falsePositives = [];

    /**
     * @var list<string>
     */
    private array $offerStatuses = [];

    /**
     * @var array<string, string>
     */
    private array $cleaners = [];
    private readonly PlaceholdersResolver $resolver;

    /**
     * @param psTrackerRegexes $trackerRegexes
     */
    public function __construct(array $trackerRegexes)
    {
        $this->resolver = new PlaceholdersResolver($trackerRegexes['placeholders']);

        $this->loadFalsePositives($trackerRegexes['false_positives']);
        $this->loadOfferStatuses($trackerRegexes['offers_statuses']);
        $this->loadCleaners($trackerRegexes['cleaners']);
    }

    /**
     * @return list<string>
     */
    public function getOfferStatuses(): array
    {
        return $this->offerStatuses;
    }

    /**
     * @return list<string>
     */
    public function getFalsePositives(): array
    {
        return $this->falsePositives;
    }

    /**
     * @return array<string, string>
     */
    public function getCleaners(): array
    {
        return $this->cleaners;
    }

    /**
     * @param list<string> $falsePositives
     */
    private function loadFalsePositives(array $falsePositives): void
    {
        $this->falsePositives = $falsePositives;
        $this->resolver->resolve($this->falsePositives);
        $this->validateRegexes($this->falsePositives);
    }

    /**
     * @param list<string> $offerStatuses
     */
    private function loadOfferStatuses(array $offerStatuses): void
    {
        $this->offerStatuses = $offerStatuses;
        $this->resolver->resolve($this->offerStatuses);
        $this->validateRegexes($this->offerStatuses);
    }

    /**
     * @param array<string, string> $cleaners
     */
    private function loadCleaners(array $cleaners): void
    {
        $regexes = array_keys($cleaners);

        $this->resolver->resolve($regexes);
        $this->validateRegexes($regexes);

        $this->cleaners = array_combine($regexes, array_values($cleaners));
    }

    /**
     * @param list<string> $regexes
     */
    private function validateRegexes(array $regexes): void
    {
        foreach ($regexes as $regex) {
            if (!Pattern::of($regex)->valid()) {
                throw new ConfigurationException("Invalid regex: $regex");
            }
        }
    }
}
