<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan;

use App\Entity\Artisan as ArtisanE;
use App\Entity\ArtisanUrl;
use App\Tests\TestUtils\Cases\TestCase;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Psl\Vec;

/**
 * @small
 */
class SmartUrlAccessorTest extends TestCase
{
    public function testGetAndSetSingleAndMultiple(): void
    {
        $artisan = Artisan::wrap($entity = new ArtisanE());

        $artisan
            ->setLinklistUrl('linklist')
            ->setWebsiteUrl('website')
            ->setPhotoUrls(['other', 'another'])
            ->setPricesUrls(['price1', 'cost2'])
            ->setFaqUrl('faq1')
        ;

        self::assertEquals([
            'URL_FAQ faq1',
            'URL_LINKLIST linklist',
            'URL_PHOTOS another',
            'URL_PHOTOS other',
            'URL_PRICES cost2',
            'URL_PRICES price1',
            'URL_WEBSITE website',
        ], $this->getUrlArray($entity));

        self::assertEquals('linklist', $artisan->getLinklistUrl());
        self::assertEquals('faq1', $artisan->getFaqUrl());
        self::assertEquals(['other', 'another'], $artisan->getPhotoUrls());
        self::assertEquals(['price1', 'cost2'], $artisan->getPricesUrls());
        self::assertEquals('website', $artisan->getWebsiteUrl());

        $artisan
            ->setLinklistUrl('') // Remove
            ->setWebsiteUrl('websiteChanged') // Set single
            ->setPhotoUrls(['other']) // Remove one from array
            ->setPricesUrls(['price3', 'price1', 'cost2']) // Add one to array
        ;

        self::assertEquals([ // Linklist removed completely
            'URL_FAQ faq1', // No change
            'URL_PHOTOS other', // Removed 'another' from array
            'URL_PRICES cost2',
            'URL_PRICES price1',
            'URL_PRICES price3', // Added to array
            'URL_WEBSITE websiteChanged', // Single changed
        ], $this->getUrlArray($entity));

        self::assertEquals('', $artisan->getLinklistUrl());
        self::assertEquals('faq1', $artisan->getFaqUrl());
        self::assertEquals(['other'], $artisan->getPhotoUrls());
        self::assertEquals(['price1', 'cost2', 'price3'], $artisan->getPricesUrls());
        self::assertEquals('websiteChanged', $artisan->getWebsiteUrl());

        $artisan
            ->setPhotoUrls([]) // Clear
        ;

        self::assertEquals([ // Other removed completely
            'URL_FAQ faq1',
            'URL_PRICES cost2',
            'URL_PRICES price1',
            'URL_PRICES price3',
            'URL_WEBSITE websiteChanged',
        ], $this->getUrlArray($entity));

        self::assertEquals([], $artisan->getPhotoUrls());
    }

    /**
     * @return string[]
     */
    private function getUrlArray(ArtisanE $artisan): array
    {
        $result = Vec\map($artisan->getUrls(), fn (ArtisanUrl $url) => $url->getType().' '.$url->getUrl());
        sort($result);

        return $result;
    }
}
