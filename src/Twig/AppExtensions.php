<?php

declare(strict_types=1);

namespace App\Twig;

use App\Filtering\FiltersData\Builder\MutableItem;
use App\Service\EnvironmentsService;
use App\Twig\Utils\Counter;
use App\Twig\Utils\HumanFriendly;
use App\Twig\Utils\SafeFor;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DataQuery;
use App\Utils\Json;
use App\Utils\Regexp\Patterns;
use JsonException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
    private readonly HumanFriendly $friendly;
    private readonly Counter $globalCounter;

    public function __construct(
        private readonly EnvironmentsService $environments,
    ) {
        $this->friendly = new HumanFriendly();
        $this->globalCounter = new Counter();
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
            new TwigFunction('get_global_counter', fn () => $this->globalCounter),
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
     * @throws JsonException
     */
    public function jsonToArtisanParametersFilter(Artisan $artisan): string
    {
        return trim(Json::encode(array_values($artisan->getPublicData())), '[]');
    }

    /**
     * @param MutableItem[] $items
     *
     * @return MutableItem[]
     */
    public function filterItemsMatchingFilter(array $items, string $matchWord): array
    {
        $pattern = Patterns::getI($matchWord);

        return array_filter($items, fn (MutableItem $item) => $pattern->test($item->label));
    }

    public function filterFilterByQuery(string $input, DataQuery $query): string
    {
        return implode(', ', $query->filterList($input));
    }
}
