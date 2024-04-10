<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Data\Definitions\Fields\Field;
use App\Filtering\DataRequests\Filters\ValueChecker\EverythingChecker;
use App\Filtering\DataRequests\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class FeaturesFilter extends AbstractFieldOptionalAndOtherFilter
{
    protected function getOwnedItems(Artisan $artisan): array
    {
        return $artisan->getFeatures();
    }

    protected function getValueChecker(array $wantedItems): ValueCheckerInterface
    {
        return new EverythingChecker($wantedItems);
    }

    protected function hasOwnedItems(Artisan $artisan): bool
    {
        return $artisan->hasData(Field::FEATURES);
    }

    protected function hasOtherOwnedItems(Artisan $artisan): bool
    {
        return $artisan->hasData(Field::OTHER_FEATURES);
    }
}
