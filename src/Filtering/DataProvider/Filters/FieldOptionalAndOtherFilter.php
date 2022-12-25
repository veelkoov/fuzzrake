<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters;

use App\Filtering\Consts;
use App\Filtering\DataProvider\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

use function Psl\Iter\contains;
use function Psl\Vec\filter;

abstract class FieldOptionalAndOtherFilter implements FilterInterface
{
    private bool $wantsUnknown;
    private bool $wantsOther;
    private ValueCheckerInterface $valueChecker;

    /**
     * @param string[] $wantedItems
     */
    public function __construct(array $wantedItems)
    {
        $this->wantsUnknown = contains($wantedItems, Consts::FILTER_VALUE_UNKNOWN);
        $this->wantsOther = contains($wantedItems, Consts::FILTER_VALUE_OTHER);

        $wantedItems = filter($wantedItems, fn (string $item) => !contains([
            Consts::FILTER_VALUE_UNKNOWN,
            Consts::FILTER_VALUE_OTHER,
        ], $item));

        $this->valueChecker = $this->getValueChecker($wantedItems);
    }

    public function matches(Artisan $artisan): bool
    {
        if ($this->wantsUnknown && '' === $this->getOwnedItems($artisan) && '' === $this->getOtherOwnedItems($artisan)) {
            return true;
        }

        $matchedOther = $this->wantsOther ? '' !== $this->getOtherOwnedItems($artisan) : null;

        return $this->valueChecker->matches($this->getOwnedItems($artisan), $matchedOther);
    }

    abstract protected function getOwnedItems(Artisan $artisan): string;

    abstract protected function getOtherOwnedItems(Artisan $artisan): string;

    /**
     * @param string[] $wantedItems
     */
    abstract protected function getValueChecker(array $wantedItems): ValueCheckerInterface;
}
