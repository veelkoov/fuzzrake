<?php

declare(strict_types=1);

namespace App\Twig;

use App\Filtering\FiltersData\Item;
use App\Repository\EventRepository;
use App\Service\EnvironmentsService;
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

    public function __construct(
        private readonly EnvironmentsService $environments,
        private readonly EventRepository $eventRepository,
    ) {
        $this->friendly = new HumanFriendly();
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
            new TwigFunction('getLatestEventTimestamp', $this->getLatestEventTimestamp(...)),
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

    public function getLatestEventTimestamp(): ?string
    {
        $timestamp = $this->eventRepository->getLatestEventTimestamp();
        if (null === $timestamp) {
            return null;
        }

        return $timestamp->format("Y-m-d H:i:s P");
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
