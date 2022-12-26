<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters;

use App\Filtering\Consts;
use App\Filtering\DataProvider\Filters\ValueChecker\AnythingChecker;
use App\Filtering\DataProvider\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

use function Psl\Iter\contains;
use function Psl\Vec\filter;

class OpenForFilter implements FilterInterface
{
    private bool $wantsNotTracked;
    private bool $wantsTrackingIssues;
    private ValueCheckerInterface $valueChecker;

    /**
     * @param string[] $wantedItems
     */
    public function __construct(array $wantedItems)
    {
        $this->wantsNotTracked = contains($wantedItems, Consts::FILTER_VALUE_NOT_TRACKED);
        $this->wantsTrackingIssues = contains($wantedItems, Consts::FILTER_VALUE_TRACKING_ISSUES);

        $wantedItems = filter($wantedItems, fn (string $item) => !contains([
            Consts::FILTER_VALUE_NOT_TRACKED,
            Consts::FILTER_VALUE_TRACKING_ISSUES,
        ], $item));

        $this->valueChecker = new AnythingChecker($wantedItems);
    }

    public function matches(Artisan $artisan): bool
    {
        if ($this->wantsNotTracked && '' === $artisan->getCommissionsUrls()) {
            return true;
        }

        if ($this->wantsTrackingIssues && $artisan->getCsTrackerIssue()) {
            return true;
        }

        return $this->valueChecker->matches($artisan->getOpenFor(), null); // TODO: Similar statuses could overlap
    }
}
