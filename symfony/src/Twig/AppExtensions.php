<?php

declare(strict_types=1);

namespace App\Twig;

use App\Data\Definitions\Ages;
use App\Data\Definitions\NewCreator;
use App\Filtering\FiltersData\Data\ItemList;
use App\Filtering\FiltersData\Item;
use App\Utils\Creator\Completeness;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Json;
use App\Utils\Regexp\Patterns;
use JsonException;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

class AppExtensions
{
    private int $uniqueInt = 1;

    #[AsTwigFilter('fragile_int')]
    public function fragileIntFilter(mixed $input): string
    {
        return is_int($input) ? (string) $input : 'unknown/error';
    }

    #[AsTwigFunction('unique_int')]
    public function getUniqueInt(): int
    {
        return $this->uniqueInt++;
    }

    #[AsTwigFunction('unknown_value', isSafe: ['html'])]
    public function unknownValue(): string
    {
        return '<i class="fas fa-question-circle" title="Unknown"></i>';
    }

    /**
     * @param string[] $primary
     * @param string[] $other
     */
    #[AsTwigFunction('comma_separated_other')]
    public function commaSeparatedOther(array $primary, array $other): string
    {
        $items = $primary;

        if ([] !== $other) {
            $items[] = 'Other'; // grep-special-label-other
        }

        $explanation = Patterns::get(' \([^)]+\)');

        return implode(', ', arr_map($items, static fn (string $item): string => $explanation->prune($item)));
    }

    #[AsTwigFunction('is_new')]
    public function isNew(Creator $creator): bool
    {
        return NewCreator::isNew($creator);
    }

    #[AsTwigFunction('has_good_completeness')]
    public function hasGoodCompleteness(Creator $creator): bool
    {
        return Completeness::hasGood($creator);
    }

    #[AsTwigFunction('completeness_text')]
    public function completenessText(Creator $creator): string
    {
        return Completeness::getCompletenessText($creator);
    }

    /**
     * @throws JsonException
     */
    #[AsTwigFunction('ab_search_uri')]
    public function abSearchUri(Creator $creator): string
    {
        $names = [$creator->getName(), ...$creator->getFormerly()];

        return 'https://bewares.getfursu.it/#search:'.Json::encode($names);
    }

    #[AsTwigFunction('get_cst_issue_text')]
    public function getCstIssueText(Creator $creator): string
    {
        if (!$creator->isTracked() || !$creator->getCsTrackerIssue()) {
            return '';
        }

        return [] !== $creator->getOpenFor() || [] !== $creator->getClosedFor() ? 'Unsure' : 'Unknown';
    }

    #[AsTwigFunction('ages_description', isSafe: ['html'])]
    public function agesDescription(Creator $creator, bool $addText): string
    {
        $result = '';

        if ($addText) {
            $result .= match ($creator->getAges()) {
                Ages::MINORS => 'Everyone is under 18',
                Ages::MIXED => 'There is a mix of people over and under 18',
                Ages::ADULTS => 'Everyone is over 18',
                default => '',
            };

            if (null === $creator->getAges()) {
                $result .= $this->unknownValue();
            }
        }

        $classes = match ($creator->getAges()) {
            Ages::MINORS => ['fa-solid fa-user-minus'],
            Ages::MIXED  => ['fa-solid fa-user-plus', 'fa-solid fa-user-minus'],
            Ages::ADULTS => [],
            default      => ['fa-solid fa-user'],
        };

        if (0 < count($classes)) {
            $result .= ' ';
        }

        foreach ($classes as $class) {
            $result .= "<i class=\"ages $class\"></i>";
        }

        return $result;
    }

    #[AsTwigFilter('filter_items_matching')]
    public function filterItemsMatchingFilter(ItemList $items, string $matchWord): ItemList
    {
        return $items->filter(static fn (Item $item) => false !== mb_stripos($item->label, $matchWord));
    }
}
