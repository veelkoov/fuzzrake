<?php

declare(strict_types=1);

namespace App\Twig;

use App\Data\Definitions\NewCreator;
use App\Filtering\FiltersData\Data\ItemList;
use App\Filtering\FiltersData\Item;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Json;
use App\Utils\Regexp\Pattern;
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

    #[AsTwigFunction('a_bug', isSafe: ['html'])]
    public function aBug(): string
    {
        return '<i class="text-danger fa-solid fa-bug" title="Unexpected value, that is probably a bug (AGAIN)"></i>';
    }

    #[AsTwigFunction('nullable_boolean_symbol', isSafe: ['html'])]
    public function nullableBooleanSymbol(?bool $input): string
    {
        return match ($input) {
            null => $this->unknownValue(),
            true => '<i class="text-success fa-solid fa-circle-check"></i>',
            false => '<i class="text-danger fa-solid fa-square-xmark"></i>',
        };
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

        $explanation = new Pattern(' \([^)]+\)');

        return implode(', ', arr_map($items, $explanation->prune(...)));
    }

    #[AsTwigFunction('is_new')]
    public function isNew(Creator $creator): bool
    {
        return NewCreator::isNew($creator);
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

    // TODO: When shown not expanded?
    //    #[AsTwigFunction('get_cst_issue_text')]
    //    public function getCstIssueText(Creator $creator): string
    //    {
    //        if (!$creator->isTracked() || !$creator->getCsTrackerIssue()) {
    //            return '';
    //        }
    //
    //        return [] !== $creator->getOpenFor() || [] !== $creator->getClosedFor() ? 'Unsure' : 'Unknown';
    //    }

    #[AsTwigFilter('filter_items_matching')]
    public function filterItemsMatchingFilter(ItemList $items, string $matchWord): ItemList
    {
        return $items->filter(static fn (Item $item) => false !== mb_stripos($item->label, $matchWord));
    }
}
