<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Data\Definitions\Fields\Field;
use App\Filtering\DataRequests\Filters\ValueChecker\AnythingChecker;
use App\Filtering\DataRequests\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class ProductionModelsFilter extends AbstractFieldOptionalAndOtherFilter
{
    protected function getOwnedItems(Artisan $artisan): array
    {
        return $artisan->getProductionModels();
    }

    protected function getValueChecker(array $wantedItems): ValueCheckerInterface
    {
        return new AnythingChecker($wantedItems);
    }

    protected function hasOwnedItems(Artisan $artisan): bool
    {
        return $artisan->hasData(Field::PRODUCTION_MODELS);
    }

    protected function hasOtherOwnedItems(Artisan $artisan): bool
    {
        return false;
    }
}
