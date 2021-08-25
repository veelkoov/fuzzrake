<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Artisan;
use App\Entity\Event;
use App\Service\EnvironmentsService;
use App\Utils\DataQuery;
use App\Utils\DateTime\DateTimeFormat;
use App\Utils\Filters\Item;
use App\Utils\Json;
use App\Utils\StringList;
use App\Utils\StrUtils;
use InvalidArgumentException;
use JsonException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
    public function __construct(
        private EnvironmentsService $environments,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('fragile_int', fn (...$args): string => $this->fragileIntFilter(...$args)),
            new TwigFilter('fragile_date', fn (...$args): string => DateTimeFormat::fragile(...$args)),
            new TwigFilter('nullable_date', fn (...$args): string => DateTimeFormat::nullable(...$args)),
            new TwigFilter('list', [$this, 'listFilter']),
            new TwigFilter('other', [$this, 'otherFilter']),
            new TwigFilter('event_url', [StrUtils::class, 'shortPrintUrl']),
            new TwigFilter('filterItemsMatching', [$this, 'filterItemsMatchingFilter']),
            new TwigFilter('humanFriendlyRegexp', [$this, 'filterHumanFriendlyRegexp']),
            new TwigFilter('filterByQuery', [$this, 'filterFilterByQuery']),
            new TwigFilter('jsonToArtisanParameters', [$this, 'jsonToArtisanParametersFilter'], ['is_safe' => ['js']]),
        ];
    }

    private function fragileIntFilter($input): string
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
            new TwigFunction('isDevEnv', [$this, 'isDevEnvFunction']),
            new TwigFunction('isDevOrTestEnv', [$this, 'isDevOrTestEnvFunction']),
            new TwigFunction('isTestEnv', [$this, 'isTestEnvFunction']),
            new TwigFunction('getCounter', [$this, 'getCounterFunction']),
            new TwigFunction('eventDescription', [$this, 'eventDescriptionFunction']),
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

    public function otherFilter($primaryList, $otherList): string
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

    public function eventDescriptionFunction(Event $event): string
    {
        if (Event::TYPE_DATA_UPDATED !== $event->getType()) {
            throw new InvalidArgumentException('Only '.Event::TYPE_DATA_UPDATED.' event type is supported by '.__FUNCTION__);
        }

        $n = $event->getNewMakersCount();
        $u = $event->getUpdatedMakersCount();
        $r = $event->getReportedUpdatedMakersCount();

        $result = '';

        if ($n) {
            $s = $n > 1 ? 's' : '';
            $result .= "$n new maker$s";
        }

        if ($n && $u) {
            $result .= ' and ';
        }

        if ($u) {
            $s = $u > 1 ? 's' : '';
            $result .= "$u updated maker$s";
        }

        if ($n || $u) {
            $s = $n + $u > 1 ? 's' : '';
            $result .= " based on received I/U request$s.";
        }

        if ($r) {
            $s = $r > 1 ? 's' : '';
            $result .= " $r maker$s updated after report$s sent by a visitor(s). Thank you for your contribution!";
        }

        return trim($result);
    }
}
