<?php

declare(strict_types=1);

namespace App\Utils\Artisan\Fields;

use App\DataDefinitions\Fields\Field;
use App\Entity\ArtisanCommissionsStatus;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Closure;

/**
 * @extends AbstractAccessor<bool,ArtisanCommissionsStatus>
 */
final class CommissionAccessor extends AbstractAccessor
{
    public static function set(Artisan $artisan, bool $isOpen, string $newValue): void
    {
        self::_set($artisan, $isOpen, $newValue);
    }

    public static function get(Artisan $artisan, bool $isOpen): string
    {
        return self::_get($artisan, $isOpen);
    }

    /**
     * @return string[]
     */
    public static function getList(Artisan $artisan, bool $isOpen): array
    {
        return self::_getList($artisan, $isOpen);
    }

    /**
     * @return ArtisanCommissionsStatus[]
     */
    public static function getObjs(Artisan $artisan, bool $isOpen): array
    {
        return parent::_getObjs($artisan, $isOpen);
    }

    protected static function getExistingItems(Artisan $artisan): array
    {
        return $artisan->getArtisan()->getCommissions()->toArray();
    }

    protected static function getItemsFilter($subset): Closure
    {
        return fn (ArtisanCommissionsStatus $status): bool => $status->getIsOpen() === $subset;
    }

    /**
     * @param ArtisanCommissionsStatus $existingItem
     */
    protected static function getValue($existingItem): string
    {
        return $existingItem->getOffer();
    }

    protected static function removeItem(Artisan $artisan, $existingItem): void
    {
        $artisan->getArtisan()->removeCommission($existingItem);
    }

    protected static function getValueCallback(): Closure
    {
        return fn (ArtisanCommissionsStatus $url): string => $url->getOffer();
    }

    protected static function addItem(Artisan $artisan, $subset, string $newValue): void
    {
        $artisan->getArtisan()->addCommission((new ArtisanCommissionsStatus())->setIsOpen($subset)->setOffer($newValue));
    }

    protected static function getFieldNameFor($subset): string
    {
        return $subset ? Field::OPEN_FOR->name : Field::CLOSED_FOR->name;
    }
}
