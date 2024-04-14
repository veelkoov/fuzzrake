<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan;

use App\Data\Definitions\Fields\Field;
use App\Entity\Artisan as ArtisanE;
use App\Entity\ArtisanUrl;
use App\Tests\TestUtils\Cases\TestCase;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

/**
 * @small
 */
class SmartUrlAccessorTest extends TestCase
{
    public function testSet(): void
    {
        $artisan = Artisan::wrap($entity = new ArtisanE());

        $artisan->setWebsiteUrl('website')
            ->setOtherUrls("other\nanother")
            ->setPricesUrls("price1\ncost2")
            ->setCommissionsUrls('commissions1');

        self::assertEquals([
            'URL_COMMISSIONS commissions1',
            'URL_OTHER another',
            'URL_OTHER other',
            'URL_PRICES cost2',
            'URL_PRICES price1',
            'URL_WEBSITE website',
        ], $this->getUrlArray($entity));

        $artisan->setWebsiteUrl('websiteChanged')
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
        ], $this->getUrlArray($entity));

        $artisan->setCommissionsUrls('')
            ->setFaqUrl("question\nwhy_new_line");

        self::assertEquals([
            'URL_FAQ question',
            'URL_FAQ why_new_line',
            'URL_OTHER other',
            'URL_PRICES another',
            'URL_PRICES price1',
            'URL_WEBSITE websiteChanged',
        ], $this->getUrlArray($entity));
    }

    public function testGet(): void
    {
        $artisan = Artisan::wrap($entity = new ArtisanE());

        $entity->addUrl($this->getNewArtisanUrl('PRICE1', Field::URL_PRICES))
            ->addUrl($this->getNewArtisanUrl('COST2', Field::URL_PRICES))
            ->addUrl($this->getNewArtisanUrl('WEBSITE', Field::URL_WEBSITE));

        self::assertEquals('WEBSITE', $artisan->getWebsiteUrl());
        self::assertEquals("PRICE1\nCOST2", $artisan->getPricesUrls());
    }

    public function testGetObjs(): void
    {
        $url1 = $this->getNewArtisanUrl('PRICE1', Field::URL_PRICES);
        $url2 = $this->getNewArtisanUrl('COST2', Field::URL_PRICES);
        $url3 = $this->getNewArtisanUrl('WEBSITE', Field::URL_WEBSITE);

        $artisan = Artisan::wrap($entity = new ArtisanE());
        $entity->addUrl($url1)->addUrl($url2)->addUrl($url3);

        self::assertEquals([$url1, $url2], array_values($artisan->getUrlObjs(Field::URL_PRICES)));
        self::assertEquals([$url3], array_values($artisan->getUrlObjs(Field::URL_WEBSITE)));
    }

    /**
     * @return string[]
     */
    private function getUrlArray(ArtisanE $artisan): array
    {
        $result = array_map(fn (ArtisanUrl $url) => $url->getType().' '.$url->getUrl(), $artisan->getUrls()->toArray());
        sort($result);

        return $result;
    }

    private function getNewArtisanUrl(string $url, Field $type): ArtisanUrl
    {
        return (new ArtisanUrl())->setUrl($url)->setType($type->value);
    }
}
