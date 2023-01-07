<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters;

use App\Filtering\Consts;
use App\Filtering\DataProvider\Filters\ValueChecker\AnythingChecker;
use App\Filtering\DataProvider\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

use function Psl\Iter\contains;
use function Psl\Vec\filter;

class LanguagesFilter implements FilterInterface
{
    private readonly bool $wantsUnknown;
    private readonly ValueCheckerInterface $valueChecker;

    /**
     * @param list<string> $wantedItems
     */
    public function __construct(array $wantedItems)
    {
        $this->wantsUnknown = contains($wantedItems, Consts::FILTER_VALUE_UNKNOWN);

        $wantedItems = filter($wantedItems, fn (string $item) => !contains([
            Consts::FILTER_VALUE_UNKNOWN,
        ], $item));

        $this->valueChecker = new AnythingChecker($wantedItems);
    }

    public function matches(Artisan $artisan): bool
    {
        if ($this->wantsUnknown && '' === $artisan->getLanguages()) {
            return true;
        }

        return $this->valueChecker->matches($artisan->getLanguages(), null);
    }
}
