<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Filtering\DataRequests\Filters\ValueChecker\EverythingChecker;
use App\Filtering\DataRequests\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class FeaturesFilter extends AbstractFieldOptionalAndOtherFilter
{
    protected function getOwnedItems(Artisan $artisan): string
    {
        return $artisan->getFeatures();
    }

    protected function getOtherOwnedItems(Artisan $artisan): string
    {
        return $artisan->getOtherFeatures();
    }

    protected function getValueChecker(array $wantedItems): ValueCheckerInterface
    {
        return new EverythingChecker($wantedItems);
    }
}
