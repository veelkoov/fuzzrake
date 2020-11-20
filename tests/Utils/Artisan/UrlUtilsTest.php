<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan;

use App\Entity\Artisan;
use App\Entity\ArtisanUrl;
use PHPUnit\Framework\TestCase;

class UrlUtilsTest extends TestCase
{
    public function testSetUrl(): void
    {
        $artisan = new Artisan();
        $artisan->setWebsiteUrl('website');
        $artisan->setOtherUrls("other\nanother");
        $artisan->setPricesUrl("price1\ncost2");
        $artisan->setCommissionsUrl('commissions1');

        self::assertEquals([
            'URL_COMMISSIONS commissions1',
            "URL_OTHER other\nanother",
            'URL_PRICES cost2',
            'URL_PRICES price1',
            'URL_WEBSITE website',
        ], $this->getUrlArray($artisan));

        $artisan->setWebsiteUrl('websiteChanged');
        $artisan->setOtherUrls('other');
        $artisan->setPricesUrl("price1\nanother");
        $artisan->setCommissionsUrl("commissions1\ncomm2addr");

        self::assertEquals([
            'URL_COMMISSIONS comm2addr',
            'URL_COMMISSIONS commissions1',
            'URL_OTHER other',
            'URL_PRICES another',
            'URL_PRICES price1',
            'URL_WEBSITE websiteChanged',
        ], $this->getUrlArray($artisan));

        $artisan->setCommissionsUrl('');
        $artisan->setFaqUrl("question\nwhy_new_line");

        self::assertEquals([
            "URL_FAQ question\nwhy_new_line",
            'URL_OTHER other',
            'URL_PRICES another',
            'URL_PRICES price1',
            'URL_WEBSITE websiteChanged',
        ], $this->getUrlArray($artisan));
    }

    private function getUrlArray(Artisan $artisan): array
    {
        $result = array_map(fn (ArtisanUrl $url) => $url->getType().' '.$url->getUrl(), $artisan->getUrls()->toArray());
        sort($result);

        return $result;
    }
}
