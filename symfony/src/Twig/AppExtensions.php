<?php

declare(strict_types=1);

namespace App\Twig;

use App\Data\Definitions\Ages;
use App\Data\Definitions\NewArtisan;
use App\Filtering\FiltersData\Item;
use App\Service\EnvironmentsService;
use App\Twig\Utils\HumanFriendly;
use App\Twig\Utils\SafeFor;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\DataQuery;
use App\Utils\Json;
use App\Utils\Regexp\Patterns;
use JsonException;
use TRegx\CleanRegex\Pattern;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use Psl\Iter;
use Psl\Vec;

class AppExtensions extends AbstractExtension
{
    const UNKNOWN_ICON = '<i class="fas fa-question-circle" title="Unknown" />';
    private readonly HumanFriendly $friendly;
    private readonly Pattern $itemExplanation;

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
            new TwigFilter('jsonToArtisanParameters', $this->jsonToArtisanParametersFilter(...), SafeFor::JS),
            new TwigFilter('ages_description', $this->agesDescription(...), SafeFor::HTML),
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
            new TwigFunction('isTestEnv', $this->isTestEnvFunction(...)),
            new TwigFunction('comma_separated_other', $this->commaSeparatedOther(...)),
            new TwigFunction('is_new', $this->isNew(...)),
            new TwigFunction('ab_search_uri', $this->abSearchUri(...)),
            new TwigFunction('get_cst_issue_text', $this->getCstIssueText(...)),
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

    public function isTestEnvFunction(): bool
    {
        return $this->environments->isTest();
    }

    /**
     * @param string[] $primary
     * @param string[] $other
     */
    public function commaSeparatedOther(array $primary, array $other): string
    {
        $items = $primary;

        if ([] !== $other) {
            $items[] = 'Other';
        }

        return implode(', ', Vec\map($items, fn(string $item): string => $this->itemExplanation->prune($item)));
    }

    public function isNew(Creator $creator): bool
    {
        return NewArtisan::isNew($creator);
    }

    /**
     * @throws JsonException
     */
    public function abSearchUri(Creator $creator): string
    {
        $names = [$creator->getName(), ...$creator->getFormerly()];

        return 'https://bewares.getfursu.it/#search:'.Json::encode($names);
    }

    public function getCstIssueText(Creator $creator): string {
        if (!$creator->isTracked() || !$creator->getCsTrackerIssue()) {
            return '';
        }

        return  [] !== $creator->getOpenFor() || [] !== $creator->getClosedFor() ? 'Unsure' : 'Unknown';
    }

    public function agesDescription(Artisan $creator, bool $addText): string
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
                $result .= self::UNKNOWN_ICON;
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
     * @throws JsonException
     */
    public function jsonToArtisanParametersFilter(Artisan $artisan): string
    {
        return trim(Json::encode(array_values($artisan->getPublicData())), '[]');
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
