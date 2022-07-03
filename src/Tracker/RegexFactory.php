<?php

declare(strict_types=1);

namespace App\Tracker;

use App\Utils\UnbelievableRuntimeException;
use Nette\Utils\Arrays;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

class RegexFactory
{
    private readonly Pattern $unnamed;
    private readonly Pattern $namedGroup;

    /**
     * @var string[]
     */
    private array $placeholders = [];

    /**
     * @var string[]
     */
    private array $falsePositives = [];

    /**
     * @var string[]
     */
    private array $offerStatuses = [];

    /**
     * @var array<string, string[]>
     */
    private readonly array $groupTranslations;

    /**
     * @var string[]
     */
    private array $usedGroupNames = [];

    /**
     * @var string[]
     */
    private array $cleaners = [];

    /**
     * @param psTrackerRegexes $trackerRegexes
     */
    public function __construct(array $trackerRegexes)
    {
        $this->namedGroup = pattern('^\$(?P<name>[a-z_]+)\$$', 'i');
        $this->unnamed = pattern('(?<!\\\\\\\\\\\\)(?<!\\\\)\\((?!\\?)'); // Known issue: can't be more than 3 "\" before the "("

        $this->groupTranslations = $trackerRegexes['group_translations'];
        $this->loadPlaceholders($trackerRegexes['placeholders']);
        $this->loadFalsePositives($trackerRegexes['false_positives']);
        $this->loadOfferStatuses($trackerRegexes['offer_statuses']);
        $this->loadCleaners($trackerRegexes['cleaners']);
        $this->validateGroupTranslations();
    }

    /**
     * @return string[]
     */
    public function getOfferStatuses(): array
    {
        return $this->offerStatuses;
    }

    /**
     * @return string[]
     */
    public function getFalsePositives(): array
    {
        return $this->falsePositives;
    }

    /**
     * @return array<string, string[]>
     */
    public function getGroupTranslations(): array
    {
        return $this->groupTranslations;
    }

    /**
     * @return string[]
     */
    public function getCleaners(): array
    {
        return $this->cleaners;
    }

    /**
     * @param array<string, psTrackerPlaceholder> $placeholders
     */
    private function loadPlaceholders(array $placeholders): void
    {
        $this->loadPlaceholderItem($placeholders, '');
        $this->resolvePlaceholders($this->placeholders);
    }

    /**
     * @param array<string, psTrackerPlaceholder>|psTrackerPlaceholder $input
     */
    private function loadPlaceholderItem($input, string $path): string
    {
        if (is_string($input)) {
            return $input;
        }

        if (!is_array($input)) {
            throw new ConfigurationException("Item of an unexpected type under path: $path");
        }

        if (0 === count($input)) {
            throw new ConfigurationException("Empty item under path: $path");
        }

        if (array_is_list($input)) {
            return $this->loadListPlaceholderItem($input, $path);
        }

        return $this->loadMapPlaceholderItem($input, $path);
    }

    /**
     * @param psTrackerPlaceholderChild[]|string[] $input
     */
    private function loadListPlaceholderItem(array $input, string $path): string
    {
        $items = Arrays::map($input, fn ($item, $idx, $arr) => $this->loadPlaceholderItem($item, "$path/$idx"));

        $group = '';

        $this->namedGroup->match($items[0])->findFirst(function (Detail $detail) use (&$group, &$items, $path): void {
            try {
                $groupName = $detail->get('name');

                if (!array_key_exists($groupName, $this->groupTranslations)) {
                    throw new ConfigurationException("Unknown group under path: $path");
                }

                if (!in_array($groupName, $this->usedGroupNames, true)) {
                    $this->usedGroupNames[] = $groupName;
                }

                $group = '?P<'.$groupName.'>';
            } catch (NonexistentGroupException $e) {
                throw new UnbelievableRuntimeException($e);
            }

            array_shift($items);
        });

        if (0 === count($items)) {
            throw new ConfigurationException("Empty item under path: $path");
        }

        return "($group".implode('|', $items).')';
    }

    /**
     * @param array<string, psTrackerPlaceholder> $input
     */
    private function loadMapPlaceholderItem(array $input, string $path): string
    {
        foreach ($input as $placeholder => $contents) {
            if (array_key_exists($placeholder, $this->placeholders)) {
                throw new ConfigurationException("Duplicated placeholder: '$placeholder'");
            }

            $this->placeholders[$placeholder] = $this->loadPlaceholderItem($contents, "$path/$placeholder");
        }

        return '('.implode('|', array_keys($input)).')';
    }

    /**
     * @param array<string, string|array<string, string>> $subject
     */
    private function resolvePlaceholders(array &$subject): void
    {
        $changed = false;

        foreach ($subject as &$resolved) {
            foreach ($this->placeholders as $placeholder => $replacement) {
                $resolved = str_replace($placeholder, $replacement, $resolved, $count);

                $changed = $changed || $count > 0;
            }
        }

        if ($changed) {
            $this->resolvePlaceholders($subject);
        }
    }

    /**
     * @param string[] $falsePositives
     */
    private function loadFalsePositives(array $falsePositives): void
    {
        $this->falsePositives = $falsePositives;
        $this->resolvePlaceholders($this->falsePositives);
        $this->setUnnamedToNoncaptured($this->falsePositives);
        $this->validateRegexes($this->falsePositives);
    }

    /**
     * @param string[] $offerStatuses
     */
    private function loadOfferStatuses(array $offerStatuses): void
    {
        $this->offerStatuses = $offerStatuses;
        $this->resolvePlaceholders($this->offerStatuses);
        $this->setUnnamedToNoncaptured($this->offerStatuses);
        $this->validateRegexes($this->offerStatuses);
    }

    /**
     * @param string[] $cleaners
     */
    private function loadCleaners(array $cleaners): void
    {
        $regexes = array_keys($cleaners);

        $this->resolvePlaceholders($regexes);
        $this->validateRegexes($regexes);

        $this->cleaners = array_combine($regexes, array_values($cleaners));
    }

    private function validateGroupTranslations(): void
    {
        foreach ($this->groupTranslations as $key => $translations) {
            if (!in_array($key, $this->usedGroupNames, true)) {
                throw new ConfigurationException("Group translations for '$key' are not used");
            }

            if (!is_array($translations)) {
                throw new ConfigurationException("Group translations data for '$key' is not an array");
            }

            if ([] === $translations) {
                throw new ConfigurationException("Group translations for '$key' are empty");
            }

            foreach ($translations as $translation) {
                if (!is_string($translation)) {
                    throw new ConfigurationException("Group translations for '$key' contain non-string items");
                }
            }
        }
    }

    /**
     * @param string[] $regexes
     */
    private function setUnnamedToNoncaptured(array &$regexes): void
    {
        foreach ($regexes as &$regex) {
            $regex = $this->unnamed->replace($regex)->all()->with('(?:');
        }
    }

    /**
     * @param string[] $regexes
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
