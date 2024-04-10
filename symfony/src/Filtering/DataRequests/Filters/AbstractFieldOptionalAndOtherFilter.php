<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Filtering\DataRequests\Consts;
use App\Filtering\DataRequests\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

abstract class AbstractFieldOptionalAndOtherFilter implements FilterInterface
{
    private readonly bool $wantsUnknown;
    private readonly bool $wantsOther;
    private readonly ValueCheckerInterface $valueChecker;

    /**
     * @param string[] $wantedItems
     */
    public function __construct(array $wantedItems)
    {
        $extractor = new SpecialItemsExtractor($wantedItems, Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);

        $this->wantsUnknown = $extractor->hasSpecial(Consts::FILTER_VALUE_UNKNOWN);
        $this->wantsOther = $extractor->hasSpecial(Consts::FILTER_VALUE_OTHER);

        $this->valueChecker = $this->getValueChecker($extractor->getCommon());
    }

    public function matches(Artisan $artisan): bool
    {
        if ($this->wantsUnknown && !$this->hasOwnedItems($artisan) && !$this->hasOtherOwnedItems($artisan)) {
            return true;
        }

        $matchedOther = $this->wantsOther ? $this->hasOtherOwnedItems($artisan) : null;

        return $this->valueChecker->matches($this->getOwnedItems($artisan), $matchedOther);
    }

    /**
     * @return list<string>
     */
    abstract protected function getOwnedItems(Artisan $artisan): array;

    abstract protected function hasOwnedItems(Artisan $artisan): bool;

    abstract protected function hasOtherOwnedItems(Artisan $artisan): bool;

    /**
     * @param string[] $wantedItems
     */
    abstract protected function getValueChecker(array $wantedItems): ValueCheckerInterface;
}
