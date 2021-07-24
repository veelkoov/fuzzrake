<?php

declare(strict_types=1);

namespace App\Utils\Accessors;

use App\DataDefinitions\Fields;
use App\Entity\Artisan;
use App\Entity\ArtisanCommissionsStatus;
use Closure;

final class Commission extends AbstractAccessor
{
    public static function set(Artisan $artisan, bool $isOpen, string $newValue): void
    {
        self::_set($artisan, $isOpen, $newValue);
    }

    public static function get(Artisan $artisan, bool $isOpen): string
    {
        return self::_get($artisan, $isOpen);
    }

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
        return $artisan->getCommissions()->toArray();
    }

    protected static function getItemsFilter($subset): Closure
    {
        return fn (ArtisanCommissionsStatus $status): bool => $status->getIsOpen() === $subset;
    }

    /**
     * @param $existingItem ArtisanCommissionsStatus
     */
    protected static function getValue($existingItem): string
    {
        return $existingItem->getOffer();
    }

    protected static function removeItem(Artisan $artisan, $existingItem): void
    {
        $artisan->removeCommission($existingItem);
    }

    protected static function getValueCallback(): Closure
    {
        return fn (ArtisanCommissionsStatus $url): string => $url->getOffer();
    }

    protected static function addItem(Artisan $artisan, $subset, string $newValue): void
    {
        $artisan->addCommission((new ArtisanCommissionsStatus())->setIsOpen($subset)->setOffer($newValue));
    }

    protected static function getFieldNameFor($subset): string
    {
        return $subset ? Fields::OPEN_FOR : Fields::CLOSED_FOR;
    }
}
