<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Entity\Artisan;
use App\Entity\ArtisanUrl;
use App\Utils\Traits\UtilityClass;

final class UrlUtils
{
    use UtilityClass;

    public static function getUrlObject(Artisan $artisan, string $urlFieldName): ?ArtisanUrl
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
        if (($url = $artisan->getSingleUrlObject($urlFieldName))) {
            return $url->getUrl();
        } else {
            return '';
        }
    }

    public static function setUrl(Artisan $artisan, string $urlFieldName, string $newUrl): void
    {
        foreach ($artisan->getUrls() as $url) {
            if ($url->getType() === $urlFieldName) {
                if ('' === $newUrl) {
                    $artisan->removeUrl($url);
                } else {
                    $url->setUrl($newUrl);
                }

                return;
            }
        }

        if ('' !== $newUrl) {
            $artisan->addUrl((new ArtisanUrl())->setType($urlFieldName)->setUrl($newUrl));
        }
    }
}
