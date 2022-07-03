<?php

declare(strict_types=1);

namespace App\Utils\Artisan\Fields;

use App\DataDefinitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;
use App\Utils\Traits\UtilityClass;
use Closure;

/**
 * @template S of string|bool
 * @template T
 */
abstract class AbstractAccessor
{
    use UtilityClass;

    /**
     * @param S $subset
     */
    public static function _set(Artisan $artisan, $subset, string $newValue): void
    {
        if (Field::from(static::getFieldNameFor($subset))->isList()) {
            $newValues = StringList::unpack($newValue);
        } else {
            $newValues = '' === $newValue ? [] : [$newValue];
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

    /**
     * @param S $subset
     */
    protected static function _get(Artisan $artisan, $subset): string
    {
        return StringList::pack(self::_getList($artisan, $subset));
    }

    /**
     * @param S $subset
     */
    protected static function _getList(Artisan $artisan, $subset): array
    {
        return array_map(static::getValueCallback(), self::_getObjs($artisan, $subset));
    }

    /**
     * @param S $subset
     */
    protected static function _getObjs(Artisan $artisan, $subset): array
    {
        return array_filter(static::getExistingItems($artisan), static::getItemsFilter($subset));
    }

    abstract protected static function getExistingItems(Artisan $artisan): array;

    /**
     * @param S $subset
     */
    abstract protected static function getItemsFilter($subset): Closure;

    /**
     * @param T $existingItem
     */
    abstract protected static function getValue($existingItem): string;

    /**
     * @param T $existingItem
     */
    abstract protected static function removeItem(Artisan $artisan, $existingItem): void;

    abstract protected static function getValueCallback(): Closure;

    /**
     * @param S $subset
     */
    abstract protected static function addItem(Artisan $artisan, $subset, string $newValue): void;

    /**
     * @param S $subset
     */
    abstract protected static function getFieldNameFor($subset): string;
}
