<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\EnvironmentsService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DataQuery;
use App\Utils\Filters\Item;
use App\Utils\Json;
use App\Utils\StringList;
use App\Utils\StrUtils;
use JsonException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
    public function __construct(
        private readonly EnvironmentsService $environments,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('fragile_int', fn (...$args): string => $this->fragileIntFilter(...$args)),
            new TwigFilter('list', $this->listFilter(...)),
            new TwigFilter('other', $this->otherFilter(...)),
            new TwigFilter('event_url', StrUtils::shortPrintUrl(...)),
            new TwigFilter('filterItemsMatching', $this->filterItemsMatchingFilter(...)),
            new TwigFilter('humanFriendlyRegexp', $this->filterHumanFriendlyRegexp(...)),
            new TwigFilter('filterByQuery', $this->filterFilterByQuery(...)),
            new TwigFilter('jsonToArtisanParameters', $this->jsonToArtisanParametersFilter(...), ['is_safe' => ['js']]),
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
            new TwigFunction('getCounter', $this->getCounterFunction(...)),
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

    public function getCounterFunction(): Counter
    {
        return new Counter();
    }

    public function otherFilter(string $primaryList, string $otherList): string
    {
        $primaryList = str_replace("\n", ', ', $primaryList);

        if ('' !== $otherList) {
            if ('' !== $primaryList) {
                return "$primaryList, Other";
            } else {
                return 'Other';
            }
        } else {
            return $primaryList;
        }
    }

    /**
     * @return string[]
     */
    public function listFilter(string $input): array
    {
        return StringList::unpack($input);
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
        $pattern = pattern($matchWord, 'i');

        return array_filter($items, fn (Item $item) => $pattern->test($item->getLabel()));
    }

    public function filterHumanFriendlyRegexp(string $input): string
    {
        $input = pattern('\(\?<!.+?\)', 'i')->prune($input);
        $input = pattern('\(\?!.+?\)', 'i')->prune($input);
        $input = pattern('\([^a-z]+?\)', 'i')->prune($input);
        $input = pattern('[()?]', 'i')->prune($input);
        $input = pattern('\[.+?\]', 'i')->prune($input);

        return strtoupper($input);
    }

    public function filterFilterByQuery(string $input, DataQuery $query): string
    {
        return implode(', ', $query->filterList($input));
    }
}
