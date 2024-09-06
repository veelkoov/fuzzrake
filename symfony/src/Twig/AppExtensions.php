<?php

declare(strict_types=1);

namespace App\Twig;

use App\Data\Definitions\Ages;
use App\Data\Definitions\NewArtisan;
use App\Filtering\FiltersData\Item;
use App\Service\EnvironmentsService;
use App\Twig\Utils\HumanFriendly;
use App\Twig\Utils\SafeFor;
use App\Utils\Artisan\Completeness;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\DataQuery;
use App\Utils\Json;
use App\Utils\Regexp\Patterns;
use JsonException;
use Psl\Vec;
use TRegx\CleanRegex\Pattern;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
    private readonly HumanFriendly $friendly;
    private readonly Pattern $itemExplanation;
    private int $uniqueInt = 1;

    public function __construct(
        private readonly EnvironmentsService $environments,
    ) {
        $this->friendly = new HumanFriendly();
        $this->itemExplanation = Pattern::of(' \([^)]+\)');
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('fragile_int', $this->fragileIntFilter(...)),
            new TwigFilter('event_url', $this->friendly->shortUrl(...)),
            new TwigFilter('filterItemsMatching', $this->filterItemsMatchingFilter(...)),
            new TwigFilter('humanFriendlyRegexp', $this->friendly->regex(...)),
            new TwigFilter('filterByQuery', $this->filterFilterByQuery(...)),
        ];
    }

    private function fragileIntFilter(mixed $input): string
    {
        if (is_int($input)) {
            return (string) $input;
        } else {
            return 'unknown/error';
        }
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isDevEnv', $this->isDevEnvFunction(...)),
            new TwigFunction('isDevOrTestEnv', $this->isDevOrTestEnvFunction(...)),

            new TwigFunction('ab_search_uri', $this->abSearchUri(...)),
            new TwigFunction('ages_description', $this->agesDescription(...), SafeFor::HTML),
            new TwigFunction('comma_separated_other', $this->commaSeparatedOther(...)),
            new TwigFunction('completeness_text', $this->completenessText(...)),
            new TwigFunction('get_cst_issue_text', $this->getCstIssueText(...)),
            new TwigFunction('has_good_completeness', $this->hasGoodCompleteness(...)),
            new TwigFunction('is_new', $this->isNew(...)),
            new TwigFunction('unique_int', fn () => $this->uniqueInt++),
            new TwigFunction('unknown_value', $this->unknownValue(...), SafeFor::HTML),
        ];
    }

    public function isDevEnvFunction(): bool
    {
        return $this->environments->isDev();
    }

    public function isDevOrTestEnvFunction(): bool
    {
        return $this->environments->isDevOrTest();
    }

    public function unknownValue(): string
    {
        return '<i class="fas fa-question-circle" title="Unknown"></i>';
    }

    /**
     * @param string[] $primary
     * @param string[] $other
     */
    public function commaSeparatedOther(array $primary, array $other): string
    {
        $items = $primary;

        if ([] !== $other) {
            $items[] = 'Other'; // grep-special-label-other
        }

        return implode(', ', Vec\map($items, fn (string $item): string => $this->itemExplanation->prune($item)));
    }

    public function isNew(Creator $creator): bool
    {
        return NewArtisan::isNew($creator);
    }

    public function hasGoodCompleteness(Creator $creator): bool
    {
        return Completeness::hasGood($creator);
    }

    public function completenessText(Creator $creator): string
    {
        return Completeness::getCompletenessText($creator);
    }

    /**
     * @throws JsonException
     */
    public function abSearchUri(Creator $creator): string
    {
        $names = [$creator->getName(), ...$creator->getFormerly()];

        return 'https://bewares.getfursu.it/#search:'.Json::encode($names);
    }

    public function getCstIssueText(Creator $creator): string
    {
        if (!$creator->isTracked() || !$creator->getCsTrackerIssue()) {
            return '';
        }

        return [] !== $creator->getOpenFor() || [] !== $creator->getClosedFor() ? 'Unsure' : 'Unknown';
    }

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

    /**
     * @param Item[] $items
     *
     * @return Item[]
     */
    public function filterItemsMatchingFilter(array $items, string $matchWord): array
    {
        $pattern = Patterns::getI($matchWord);

        return array_filter($items, fn (Item $item) => $pattern->test($item->label));
    }

    /**
     * @param list<string> $input
     */
    public function filterFilterByQuery(array $input, DataQuery $query): string
    {
        return implode(', ', $query->filterList($input));
    }
}
