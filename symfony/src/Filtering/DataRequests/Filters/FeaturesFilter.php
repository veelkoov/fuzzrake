<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Data\Definitions\Fields\Field;
use App\Filtering\DataRequests\Filters\ValueChecker\EverythingChecker;
use App\Filtering\DataRequests\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Override;

class FeaturesFilter extends AbstractFieldOptionalAndOtherFilter
{
    #[Override]
    protected function getOwnedItems(Artisan $artisan): array
    {
        return $artisan->getFeatures();
    }

    #[Override]
    protected function getValueChecker(array $wantedItems): ValueCheckerInterface
    {
        return new EverythingChecker($wantedItems);
    }

    #[Override]
    protected function hasOwnedItems(Artisan $artisan): bool
    {
        return $artisan->hasData(Field::FEATURES);
    }

    #[Override]
    protected function hasOtherOwnedItems(Artisan $artisan): bool
    {
        return $artisan->hasData(Field::OTHER_FEATURES);
    }
}
