<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Entity\ArtisanCommissionsStatus as ACS;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;
use App\Utils\Traits\UtilityClass;

final class SmartOfferStatusAccessor
{
    use UtilityClass;

    /**
     * @return list<ACS>
     */
    public static function getObjects(Artisan $artisan, bool $isOpen): array
    {
        return array_filter($artisan->getArtisan()->getCommissions()->toArray(),
            fn (ACS $status): bool => $status->getIsOpen() === $isOpen);
    }

    /**
     * @return list<string>
     */
    public static function getList(Artisan $artisan, bool $isOpen): array
    {
        return array_map(fn (ACS $url) => $url->getOffer(), self::getObjects($artisan, $isOpen));
    }

    public static function getPacked(Artisan $artisan, bool $isOpen): string
    {
        return StringList::pack(self::getList($artisan, $isOpen));
    }

    public static function setPacked(Artisan $artisan, bool $isOpen, string $newValue): void
    {
        $newValues = StringList::unpack($newValue);

        $existingValues = self::getObjects($artisan, $isOpen);

        foreach ($existingValues as $existingUrl) {
            if (!in_array($existingUrl->getOffer(), $newValues)) {
                $artisan->getArtisan()->removeCommission($existingUrl);
            }
        }

        $existingValues = array_map(fn (ACS $url): string => $url->getOffer(), $existingValues);

        foreach ($newValues as $newValue) {
            if (!in_array($newValue, $existingValues)) {
                $artisan->getArtisan()->addCommission((new ACS())->setIsOpen($isOpen)->setOffer($newValue));
            }
        }
    }
}
