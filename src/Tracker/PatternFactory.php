<?php

declare(strict_types=1);

namespace App\Tracker;

use App\Utils\UnbelievableRuntimeException;
use Nette\Utils\Arrays;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

class PatternFactory
{
    private Pattern $namedGroup;

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
     * @var string[][]
     */
    private array $groupTranslations;

    /**
     * @var string[]
     */
    private array $usedGroupNames = [];

    public function __construct(array $trackerRegexes)
    {
        $this->namedGroup = pattern('^\$(?P<name>[a-z_]+)\$$', 'i');

        $this->groupTranslations = $trackerRegexes['group_translations'];
        $this->loadPlaceholders($trackerRegexes['placeholders']);
        $this->loadFalsePositives($trackerRegexes['false_positives']);
        $this->loadOfferStatuses($trackerRegexes['offer_statuses']);
        $this->validateGroupTranslations();
        // TODO: Automate changing non-named groups to non-capturing groups
    }

    /**
     * @return Pattern[]
     */
    public function getOfferStatuses(): array
    {
        return Arrays::map($this->offerStatuses, fn ($item, $idx, $arr) => pattern($item, 's'));
    }

    /**
     * @return Pattern[]
     */
    public function getFalsePositives(): array
    {
        return Arrays::map($this->falsePositives, fn ($item, $idx, $arr) => pattern($item, 's'));
    }

    public function getGroupTranslations(): array
    {
        return $this->groupTranslations;
    }

    private function loadPlaceholders(array $placeholders): void
    {
        $this->loadPlaceholderItem($placeholders, '');
        $this->resolvePlaceholders($this->placeholders);
    }

    private function loadPlaceholderItem(mixed $input, string $path): string
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

    private function loadFalsePositives(array $falsePositives): void
    {
        $this->falsePositives = $falsePositives;
        $this->resolvePlaceholders($this->falsePositives);
    }

    private function loadOfferStatuses(array $offerStatuses): void
    {
        $this->offerStatuses = $offerStatuses;
        $this->resolvePlaceholders($this->offerStatuses);
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
}
