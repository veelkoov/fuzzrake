<?php

declare(strict_types=1);

namespace App\Tracking\Regex;

use App\Tracking\Exception\ConfigurationException;
use Psl\Type;
use TRegx\CleanRegex\Match\Detail;
use TRegx\CleanRegex\Pattern;

class RegexFactory
{
    private readonly Pattern $namedGroup;

    /**
     * @var array<string, string>
     */
    private array $placeholders = [];

    /**
     * @var list<string>
     */
    private array $falsePositives = [];

    /**
     * @var list<string>
     */
    private array $offerStatuses = [];

    /**
     * @var array<string, list<string>>
     */
    private readonly array $groupsTranslations;

    /**
     * @var list<string>
     */
    private array $usedGroupNames = [];

    /**
     * @var array<string, string>
     */
    private array $cleaners = [];

    /**
     * @param psTrackerRegexes $trackerRegexes
     */
    public function __construct(array $trackerRegexes)
    {
        $this->namedGroup = pattern('^\?P<(?P<group_name>[a-z_]+)>(?P<placeholder>.+)$', 'in');

        $this->groupsTranslations = $trackerRegexes['matched_group_name_to_offers_or_status'];
        $this->loadPlaceholders($trackerRegexes['placeholders']);
        $this->loadFalsePositives($trackerRegexes['false_positives']);
        $this->loadOfferStatuses($trackerRegexes['offers_statuses']);
        $this->loadCleaners($trackerRegexes['cleaners']);
        $this->validateGroupTranslations();
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
     * @return array<string, list<string>>
     */
    public function getGroupsTranslations(): array
    {
        return $this->groupsTranslations;
    }

    /**
     * @return array<string, string>
     */
    public function getCleaners(): array
    {
        return $this->cleaners;
    }

    /**
     * @param psTrckRgxsPlaceholders $placeholders
     */
    private function loadPlaceholders(array $placeholders): void
    {
        $shape = Type\non_empty_dict(
            Type\non_empty_string(),
            Type\union(
                Type\non_empty_vec(Type\non_empty_string()),
                Type\non_empty_dict(
                    Type\non_empty_string(),
                    Type\non_empty_vec(Type\non_empty_string()),
                ),
            ),
        );

        $placeholders = $shape->assert($placeholders);

        $this->loadPlaceholderItem($placeholders, '', '');
        $this->resolvePlaceholders($this->placeholders);
    }

    /**
     * @param psTrckRgxsPlaceholders|array<string, list<string>>|list<string> $input
     */
    private function loadPlaceholderItem(array $input, string $groupName, string $path): string
    {
        if (array_is_list($input)) {
            /** @var list<string> $input grep-phpstan-var-typing */

            return $this->alternative($input, $groupName);
        }

        /** @var psTrckRgxsPlaceholders|array<string, list<string>> $input grep-phpstan-var-typing */
        return $this->loadMapPlaceholderItem($input, $groupName, $path);
    }

    /**
     * @param psTrckRgxsPlaceholders|array<string, list<string>> $input
     */
    private function loadMapPlaceholderItem(array $input, string $groupName, string $path): string
    {
        $placeholders = [];

        foreach ($input as $placeholder => $contents) {
            $subItemNamedGroup = '';

            $this->namedGroup->match($placeholder)->findFirst()
                ->map(function (Detail $detail) use (&$placeholder, &$subItemNamedGroup) {
                    $placeholder = $detail->group('placeholder')->text();
                    $subItemNamedGroup = $detail->group('group_name')->text();

                    if (!in_array($subItemNamedGroup, $this->usedGroupNames, true)) {
                        $this->usedGroupNames[] = $subItemNamedGroup;
                    }
                });

            if (array_key_exists($placeholder, $this->placeholders)) {
                throw new ConfigurationException("Duplicated placeholder: '$placeholder'");
            }

            $placeholders[] = $placeholder;

            $this->placeholders[$placeholder] = $this->loadPlaceholderItem($contents, $subItemNamedGroup, "$path/$placeholder");
        }

        return $this->alternative($placeholders, $groupName);
    }

    /**
     * @param list<string> $items
     */
    private function alternative(array $items, string $groupName): string
    {
        $groupName = '' === $groupName ? '' : "?P<$groupName>";

        return "($groupName".implode('|', $items).')';
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
     * @param list<string> $falsePositives
     */
    private function loadFalsePositives(array $falsePositives): void
    {
        $this->falsePositives = $falsePositives;
        $this->resolvePlaceholders($this->falsePositives);
        $this->validateRegexes($this->falsePositives);
    }

    /**
     * @param list<string> $offerStatuses
     */
    private function loadOfferStatuses(array $offerStatuses): void
    {
        $this->offerStatuses = $offerStatuses;
        $this->resolvePlaceholders($this->offerStatuses);
        $this->validateRegexes($this->offerStatuses);
    }

    /**
     * @param array<string, string> $cleaners
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
        foreach ($this->groupsTranslations as $key => $translations) {
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
