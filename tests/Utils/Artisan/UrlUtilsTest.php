<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan;

use App\Entity\Artisan;
use App\Entity\ArtisanUrl;
use App\Utils\Artisan\Fields;
use PHPUnit\Framework\TestCase;

class UrlUtilsTest extends TestCase
{
    public function testSetUrl(): void
    {
        $artisan = new Artisan();
        $artisan->setWebsiteUrl('website')
            ->setOtherUrls("other\nanother")
            ->setPricesUrl("price1\ncost2")
            ->setCommissionsUrl('commissions1');

        self::assertEquals([
            'URL_COMMISSIONS commissions1',
            "URL_OTHER other\nanother",
            'URL_PRICES cost2',
            'URL_PRICES price1',
            'URL_WEBSITE website',
        ], $this->getUrlArray($artisan));

        $artisan->setWebsiteUrl('websiteChanged')
            ->setOtherUrls('other')
            ->setPricesUrl("price1\nanother")
            ->setCommissionsUrl("commissions1\ncomm2addr");

        self::assertEquals([
            'URL_COMMISSIONS comm2addr',
            'URL_COMMISSIONS commissions1',
            'URL_OTHER other',
            'URL_PRICES another',
            'URL_PRICES price1',
            'URL_WEBSITE websiteChanged',
        ], $this->getUrlArray($artisan));

        $artisan->setCommissionsUrl('')
            ->setFaqUrl("question\nwhy_new_line");

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

    public function testGetUrl(): void
    {
        $artisan = new Artisan();
        $artisan->addUrl($this->getNewArtisanUrl('PRICE1', Fields::URL_PRICES))
            ->addUrl($this->getNewArtisanUrl('COST2', Fields::URL_PRICES))
            ->addUrl($this->getNewArtisanUrl('WEBSITE', Fields::URL_WEBSITE));

        self::assertEquals('WEBSITE', $artisan->getWebsiteUrl());
        self::assertEquals("PRICE1\nCOST2", $artisan->getPricesUrl());
    }

    public function testGetUrlObjs(): void
    {
        $url1 = $this->getNewArtisanUrl('PRICE1', Fields::URL_PRICES);
        $url2 = $this->getNewArtisanUrl('COST2', Fields::URL_PRICES);
        $url3 = $this->getNewArtisanUrl('WEBSITE', Fields::URL_WEBSITE);

        $artisan = new Artisan();
        $artisan->addUrl($url1)->addUrl($url2)->addUrl($url3);

        self::assertEquals([$url1, $url2], array_values($artisan->getUrlObjs(Fields::URL_PRICES)));
        self::assertEquals([$url3], array_values($artisan->getUrlObjs(Fields::URL_WEBSITE)));
    }

    private function getNewArtisanUrl(string $url, string $type): ArtisanUrl
    {
        return (new ArtisanUrl())->setUrl($url)->setType($type);
    }
}
