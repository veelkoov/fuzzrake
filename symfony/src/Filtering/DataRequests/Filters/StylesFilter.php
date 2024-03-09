<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Filtering\DataRequests\Filters\ValueChecker\AnythingChecker;
use App\Filtering\DataRequests\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class StylesFilter extends AbstractFieldOptionalAndOtherFilter
{
    protected function getOwnedItems(Artisan $artisan): string
    {
        return $artisan->getStyles();
    }

    protected function getOtherOwnedItems(Artisan $artisan): string
    {
        return $artisan->getOtherStyles();
    }

    protected function getValueChecker(array $wantedItems): ValueCheckerInterface
    {
        return new AnythingChecker($wantedItems);
    }
}
