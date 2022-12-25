<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters;

use App\Filtering\DataProvider\Filters\ValueChecker\AnythingChecker;
use App\Filtering\DataProvider\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class StylesFilter extends FieldOptionalAndOtherFilter
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
