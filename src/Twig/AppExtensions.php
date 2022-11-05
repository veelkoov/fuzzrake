<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\EnvironmentsService;
use App\Twig\Utils\Counter;
use App\Twig\Utils\HumanReadableRegexes;
use App\Twig\Utils\SafeFor;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DataQuery;
use App\Utils\Filters\Item;
use App\Utils\Json;
use App\Utils\Regexp\Patterns;
use App\Utils\StringList;
use App\Utils\StrUtils;
use JsonException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
    private readonly HumanReadableRegexes $humanReadableRegexes;

    public function __construct(
        private readonly EnvironmentsService $environments,
    ) {
        $this->humanReadableRegexes = new HumanReadableRegexes();
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('fragile_int', $this->fragileIntFilter(...)),
            new TwigFilter('list', $this->listFilter(...)),
            new TwigFilter('other', $this->otherFilter(...)),
            new TwigFilter('event_url', StrUtils::shortPrintUrl(...)),
            new TwigFilter('filterItemsMatching', $this->filterItemsMatchingFilter(...)),
            new TwigFilter('humanFriendlyRegexp', $this->humanReadableRegexes->makeReadable(...)),
            new TwigFilter('filterByQuery', $this->filterFilterByQuery(...)),
            new TwigFilter('jsonToArtisanParameters', $this->jsonToArtisanParametersFilter(...), SafeFor::JS),
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
        $pattern = Patterns::getI($matchWord);

        return array_filter($items, fn (Item $item) => $pattern->test($item->getLabel()));
    }

    public function filterFilterByQuery(string $input, DataQuery $query): string
    {
        return implode(', ', $query->filterList($input));
    }
}
