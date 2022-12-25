<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters;

use App\Filtering\DataProvider\Filters\ValueChecker\AnythingChecker;
use App\Filtering\DataProvider\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class ProductionModelsFilter extends FieldOptionalAndOtherFilter
{
    protected function getOwnedItems(Artisan $artisan): string
    {
        return $artisan->getProductionModels();
    }

    protected function getOtherOwnedItems(Artisan $artisan): string
    {
        return '';
    }

    protected function getValueChecker(array $wantedItems): ValueCheckerInterface
    {
        return new AnythingChecker($wantedItems);
    }
}
