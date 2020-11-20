<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Entity\Artisan;
use App\Entity\ArtisanUrl;
use App\Utils\StringList;
use App\Utils\Traits\UtilityClass;

final class UrlUtils
{
    use UtilityClass;

    /**
     * @return ArtisanUrl[]
     */
    public static function getUrlObjs(Artisan $artisan, string $urlFieldName): array
    {
        return array_filter($artisan->getUrls()->toArray(), fn (ArtisanUrl $url) => $url->getType() === $urlFieldName);
    }

    public static function getUrl(Artisan $artisan, string $urlFieldName): string
    {
        return StringList::pack(array_map(fn (ArtisanUrl $url) => $url->getUrl(), self::getUrlObjs($artisan, $urlFieldName)));
    }

    public static function setUrl(Artisan $artisan, string $urlFieldName, string $newUrl): void
    {
        if (Fields::get($urlFieldName)->isList()) {
            $newUrls = StringList::unpack($newUrl);
        } else {
            $newUrls = [$newUrl];
        }

        $existingUrls = array_filter($artisan->getUrls()->toArray(), fn (ArtisanUrl $url): bool => $url->getType() === $urlFieldName);

        foreach ($existingUrls as $existingUrl) {
            if (!in_array($existingUrl->getUrl(), $newUrls)) {
                $artisan->removeUrl($existingUrl);
            }
        }

        $existingUrls = array_map(fn (ArtisanUrl $url): string => $url->getUrl(), $existingUrls);

        foreach ($newUrls as $newUrl) {
            if (!in_array($newUrl, $existingUrls)) {
                $artisan->addUrl((new ArtisanUrl())->setType($urlFieldName)->setUrl($newUrl));
            }
        }
    }
}
