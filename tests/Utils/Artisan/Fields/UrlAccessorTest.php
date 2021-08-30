<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan\Fields;

use App\DataDefinitions\Fields;
use App\Entity\Artisan as ArtisanE;
use App\Entity\ArtisanUrl;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\ArtisanChanges;
use PHPUnit\Framework\TestCase;

/**
 * @see AbstractAccessor
 * @see ArtisanChanges
 * @see UrlAccessor
 */
class UrlAccessorTest extends TestCase
{
    public function testSet(): void
    {
        $smart = Artisan::wrap($artisan = new ArtisanE());

        $smart->setWebsiteUrl('website')
            ->setOtherUrls("other\nanother")
            ->setPricesUrls("price1\ncost2")
            ->setCommissionsUrls('commissions1');

        self::assertEquals([
            'URL_COMMISSIONS commissions1',
            "URL_OTHER other\nanother",
            'URL_PRICES cost2',
            'URL_PRICES price1',
            'URL_WEBSITE website',
        ], $this->getUrlArray($artisan));

        $smart->setWebsiteUrl('websiteChanged')
            ->setOtherUrls('other')
            ->setPricesUrls("price1\nanother")
            ->setCommissionsUrls("commissions1\ncomm2addr");

        self::assertEquals([
            'URL_COMMISSIONS comm2addr',
            'URL_COMMISSIONS commissions1',
            'URL_OTHER other',
            'URL_PRICES another',
            'URL_PRICES price1',
            'URL_WEBSITE websiteChanged',
        ], $this->getUrlArray($artisan));

        $smart->setCommissionsUrls('')
            ->setFaqUrl("question\nwhy_new_line");

        self::assertEquals([
            "URL_FAQ question\nwhy_new_line",
            'URL_OTHER other',
            'URL_PRICES another',
            'URL_PRICES price1',
            'URL_WEBSITE websiteChanged',
        ], $this->getUrlArray($artisan));
    }

    public function testGet(): void
    {
        $smart = Artisan::wrap($artisan = new ArtisanE());

        $artisan->addUrl($this->getNewArtisanUrl('PRICE1', Fields::URL_PRICES))
            ->addUrl($this->getNewArtisanUrl('COST2', Fields::URL_PRICES))
            ->addUrl($this->getNewArtisanUrl('WEBSITE', Fields::URL_WEBSITE));

        self::assertEquals('WEBSITE', $smart->getWebsiteUrl());
        self::assertEquals("PRICE1\nCOST2", $smart->getPricesUrls());
    }

    public function testGetObjs(): void
    {
        $url1 = $this->getNewArtisanUrl('PRICE1', Fields::URL_PRICES);
        $url2 = $this->getNewArtisanUrl('COST2', Fields::URL_PRICES);
        $url3 = $this->getNewArtisanUrl('WEBSITE', Fields::URL_WEBSITE);

        $smart = Artisan::wrap($artisan = new ArtisanE());
        $artisan->addUrl($url1)->addUrl($url2)->addUrl($url3);

        self::assertEquals([$url1, $url2], array_values($smart->getUrlObjs(Fields::URL_PRICES)));
        self::assertEquals([$url3], array_values($smart->getUrlObjs(Fields::URL_WEBSITE)));
    }

    private function getUrlArray(ArtisanE $artisan): array
    {
        $result = array_map(fn (ArtisanUrl $url) => $url->getType().' '.$url->getUrl(), $artisan->getUrls()->toArray());
        sort($result);

        return $result;
    }

    private function getNewArtisanUrl(string $url, string $type): ArtisanUrl
    {
        return (new ArtisanUrl())->setUrl($url)->setType($type);
    }
}
