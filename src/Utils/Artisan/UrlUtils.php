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

    public static function getUrlObj(Artisan $artisan, string $urlFieldName): ?ArtisanUrl
    {
        foreach ($artisan->getUrls() as $url) {
            if ($url->getType() === $urlFieldName) {
                return $url;
            }
        }

        return null;
    }

    public static function getUrl(Artisan $artisan, string $urlFieldName): string
    {
        if (($url = $artisan->getUrlObj($urlFieldName))) {
            return $url->getUrl();
        } else {
            return '';
        }
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
