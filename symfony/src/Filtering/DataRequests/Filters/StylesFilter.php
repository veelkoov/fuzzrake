<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Data\Definitions\Fields\Field;
use App\Filtering\DataRequests\Filters\ValueChecker\AnythingChecker;
use App\Filtering\DataRequests\Filters\ValueChecker\ValueCheckerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class StylesFilter extends AbstractFieldOptionalAndOtherFilter
{
    /**
     * @return list<string>
     */
    protected function getOwnedItems(Artisan $artisan): array
    {
        return $artisan->getStyles();
    }

    protected function getValueChecker(array $wantedItems): ValueCheckerInterface
    {
        return new AnythingChecker($wantedItems);
    }

    protected function hasOwnedItems(Artisan $artisan): bool
    {
        return $artisan->hasData(Field::STYLES);
    }

    protected function hasOtherOwnedItems(Artisan $artisan): bool
    {
        return $artisan->hasData(Field::OTHER_STYLES);
    }
}
