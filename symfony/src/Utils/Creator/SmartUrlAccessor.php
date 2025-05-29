<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Entity\CreatorUrl as ItemType;
use App\Utils\Collections\Arrays;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;
use Psl\Vec;

final class SmartUrlAccessor
{
    use UtilityClass;

    /**
     * @return list<ItemType>
     */
    public static function getObjects(Creator $creator, string $type): array
    {
        return Vec\filter($creator->getCreator()->getUrls(),
            fn (ItemType $url): bool => $url->getType() === $type);
    }

    /**
     * @return list<string>
     */
    public static function getList(Creator $creator, string $type): array
    {
        return array_map(fn (ItemType $item) => $item->getUrl(), self::getObjects($creator, $type));
    }

    /**
     * @param list<string> $newUrls
     */
    public static function setList(Creator $creator, string $type, array $newUrls): void
    {
        // Note reordering of miniatures: grep-code-order-support-workaround
        $existingObjects = self::getObjects($creator, $type);

        foreach ($existingObjects as $existingObject) {
            if (!in_array($existingObject->getUrl(), $newUrls, true)) {
                $creator->getCreator()->removeUrl($existingObject);
            }
        }

        $existingValues = self::getList($creator, $type);

        foreach ($newUrls as $newValue) {
            if (!in_array($newValue, $existingValues, true)) {
                $newObject = (new ItemType())->setType($type)->setUrl($newValue);

                $creator->getCreator()->addUrl($newObject);
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
