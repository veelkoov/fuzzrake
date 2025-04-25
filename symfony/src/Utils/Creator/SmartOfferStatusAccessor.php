<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Entity\CreatorOfferStatus as ItemType;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;
use Psl\Vec;

final class SmartOfferStatusAccessor
{
    use UtilityClass;

    /**
     * @return list<ItemType>
     */
    private static function getObjects(Creator $creator, bool $isOpen): array
    {
        return Vec\filter($creator->getCreator()->getOfferStatuses(),
            fn (ItemType $status): bool => $status->getIsOpen() === $isOpen);
    }

    /**
     * @return list<string>
     */
    public static function getList(Creator $creator, bool $isOpen): array
    {
        return array_map(fn (ItemType $item) => $item->getOffer(), self::getObjects($creator, $isOpen));
    }

    /**
     * @param list<string> $newOffers
     */
    public static function setList(Creator $creator, bool $isOpen, array $newOffers): void
    {
        $existingObjects = self::getObjects($creator, $isOpen);

        foreach ($existingObjects as $existingObject) {
            if (!in_array($existingObject->getOffer(), $newOffers, true)) {
                $creator->getCreator()->removeOfferStatus($existingObject);
            }
        }

        $existingValues = self::getList($creator, $isOpen);

        foreach ($newOffers as $newValue) {
            if (!in_array($newValue, $existingValues, true)) {
                $newObject = (new ItemType())->setIsOpen($isOpen)->setOffer($newValue);

                $creator->getCreator()->addOfferStatus($newObject);
            }
        }
    }
}
