<?php

declare(strict_types=1);

namespace App\Utils\Accessors;

use App\DataDefinitions\Fields;
use App\Entity\Artisan;
use App\Utils\StringList;
use App\Utils\Traits\UtilityClass;
use Closure;

abstract class AbstractAccessor
{
    use UtilityClass;

    public static function _set(Artisan $artisan, $subset, string $newValue): void
    {
        if (Fields::get(static::getFieldNameFor($subset))->isList()) {
            $newValues = StringList::unpack($newValue);
        } else {
            $newValues = [$newValue];
        }

        $existingValues = array_filter(static::getExistingItems($artisan), static::getItemsFilter($subset));

        foreach ($existingValues as $existingUrl) {
            if (!in_array(static::getValue($existingUrl), $newValues)) {
                static::removeItem($artisan, $existingUrl);
            }
        }

        $existingValues = array_map(static::getValueCallback(), $existingValues);

        foreach ($newValues as $newValue) {
            if (!in_array($newValue, $existingValues)) {
                static::addItem($artisan, $subset, $newValue);
            }
        }
    }

    protected static function _get(Artisan $artisan, $subset): string
    {
        return StringList::pack(self::_getList($artisan, $subset));
    }

    protected static function _getList(Artisan $artisan, $subset): array
    {
        return array_map(static::getValueCallback(), self::_getObjs($artisan, $subset));
    }

    protected static function _getObjs(Artisan $artisan, $subset): array
    {
        return array_filter(static::getExistingItems($artisan), static::getItemsFilter($subset));
    }

    abstract protected static function getExistingItems(Artisan $artisan): array;

    abstract protected static function getItemsFilter($subset): Closure;

    abstract protected static function getValue($existingItem): string;

    abstract protected static function removeItem(Artisan $artisan, $existingItem): void;

    abstract protected static function getValueCallback(): Closure;

    abstract protected static function addItem(Artisan $artisan, $subset, string $newValue): void;

    abstract protected static function getFieldNameFor($subset): string;
}
