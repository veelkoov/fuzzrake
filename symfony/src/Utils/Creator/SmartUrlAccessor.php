<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Entity\CreatorUrl as ItemType;
use App\Utils\Collections\Arrays;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;

final class SmartUrlAccessor
{
    use UtilityClass;

    /**
     * @return list<ItemType>
     */
    public static function getObjects(Creator $creator, string $type): array
    {
        return iter_filter($creator->entity->getUrls(),
            static fn (ItemType $url): bool => $url->getType() === $type);
    }

    /**
     * @return list<string>
     */
    public static function getList(Creator $creator, string $type): array
    {
        return arr_map(self::getObjects($creator, $type), static fn (ItemType $item) => $item->getUrl());
    }

    /**
     * @param list<string> $newUrls
     */
    public static function setList(Creator $creator, string $type, array $newUrls): void
    {
        // Note reordering of miniatures; grep-code-order-support-workaround
        $existingObjects = self::getObjects($creator, $type);

        foreach ($existingObjects as $existingObject) {
            if (!arr_contains($newUrls, $existingObject->getUrl())) {
                $creator->entity->removeUrl($existingObject);
            }
        }

        $existingValues = self::getList($creator, $type);

        foreach ($newUrls as $newValue) {
            if (!arr_contains($existingValues, $newValue)) {
                $newObject = new ItemType()->setType($type)->setUrl($newValue);

                $creator->entity->addUrl($newObject);
            }
        }
    }

    public static function getSingle(Creator $creator, string $type): string
    {
        $result = self::getList($creator, $type);

        return [] === $result ? '' : Arrays::single($result);
    }

    public static function setSingle(Creator $creator, string $type, string $newUrl): void
    {
        self::setList($creator, $type, '' === $newUrl ? [] : [$newUrl]);
    }
}
