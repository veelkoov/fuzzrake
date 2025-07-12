<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Entity\CreatorOfferStatus as ItemType;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;

final class SmartOfferStatusAccessor
{
    use UtilityClass;

    /**
     * @return list<ItemType>
     */
    private static function getObjects(Creator $creator, bool $isOpen): array
    {
        return iter_filterl($creator->entity->getOfferStatuses(),
            static fn (ItemType $status): bool => $status->getIsOpen() === $isOpen);
    }

    /**
     * @return list<string>
     */
    public static function getList(Creator $creator, bool $isOpen): array
    {
        return arr_map(self::getObjects($creator, $isOpen), static fn (ItemType $item) => $item->getOffer());
    }

    /**
     * @param list<string> $newOffers
     */
    public static function setList(Creator $creator, bool $isOpen, array $newOffers): void
    {
        $existingObjects = self::getObjects($creator, $isOpen);

        foreach ($existingObjects as $existingObject) {
            if (!arr_contains($newOffers, $existingObject->getOffer())) {
                $creator->entity->removeOfferStatus($existingObject);
            }
        }

        $existingValues = self::getList($creator, $isOpen);

        foreach ($newOffers as $newValue) {
            if (!arr_contains($existingValues, $newValue)) {
                $newObject = new ItemType()->setIsOpen($isOpen)->setOffer($newValue);

                $creator->entity->addOfferStatus($newObject);
            }
        }
    }
}
