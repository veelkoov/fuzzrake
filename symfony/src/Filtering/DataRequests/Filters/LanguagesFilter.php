<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Data\Definitions\Fields\Field;
use App\Filtering\DataRequests\Consts;
use App\Filtering\DataRequests\Filters\ValueChecker\AnythingChecker;
use App\Filtering\DataRequests\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Override;

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

    #[Override]
    public function matches(Artisan $artisan): bool
    {
        if ($this->wantsUnknown && !$artisan->hasData(Field::LANGUAGES)) {
            return true;
        }

        return $this->valueChecker->matches($artisan->getLanguages(), null);
    }
}
