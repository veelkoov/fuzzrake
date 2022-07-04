<?php

declare(strict_types=1);

namespace App\Utils\Artisan\Fields;

use App\Entity\ArtisanUrl;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Closure;

/**
 * @extends AbstractAccessor<string, ArtisanUrl>
 */
final class UrlAccessor extends AbstractAccessor
{
    public static function set(Artisan $artisan, string $urlFieldName, string $newValue): void
    {
        self::_set($artisan, $urlFieldName, $newValue);
    }

    public static function get(Artisan $artisan, string $urlFieldName): string
    {
        return self::_get($artisan, $urlFieldName);
    }

    /**
     * @return string[]
     */
    public static function getList(Artisan $artisan, string $urlFieldName): array
    {
        return self::_getList($artisan, $urlFieldName);
    }

    /**
     * @return ArtisanUrl[]
     */
    public static function getObjs(Artisan $artisan, string $urlFieldName): array
    {
        return parent::_getObjs($artisan, $urlFieldName);
    }

    protected static function getExistingItems(Artisan $artisan): array
    {
        return $artisan->getArtisan()->getUrls()->toArray();
    }

    protected static function getItemsFilter($subset): Closure
    {
        return fn (ArtisanUrl $url): bool => $url->getType() === $subset;
    }

    /**
     * @param ArtisanUrl $existingItem
     */
    protected static function getValue($existingItem): string
    {
        return $existingItem->getUrl();
    }

    protected static function removeItem(Artisan $artisan, mixed $existingItem): void
    {
        $artisan->getArtisan()->removeUrl($existingItem);
    }

    protected static function getValueCallback(): Closure
    {
        return fn (ArtisanUrl $url): string => $url->getUrl();
    }

    protected static function addItem(Artisan $artisan, $subset, string $newValue): void
    {
        $artisan->getArtisan()->addUrl((new ArtisanUrl())->setType($subset)->setUrl($newValue));
    }

    protected static function getFieldNameFor($subset): string
    {
        return $subset;
    }
}
