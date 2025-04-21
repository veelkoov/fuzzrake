<?php

declare(strict_types=1);

namespace App\Tests\Utils\Creator;

use App\Entity\Creator as CreatorE;
use App\Entity\CreatorUrl;
use App\Tests\TestUtils\Cases\TestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Psl\Vec;

/**
 * @small
 */
class SmartUrlAccessorTest extends TestCase
{
    public function testGetAndSetSingleAndMultiple(): void
    {
        $creator = Creator::wrap($entity = new CreatorE());

        $creator
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

        self::assertEquals('linklist', $creator->getLinklistUrl());
        self::assertEquals('faq1', $creator->getFaqUrl());
        self::assertEquals(['other', 'another'], $creator->getPhotoUrls());
        self::assertEquals(['price1', 'cost2'], $creator->getPricesUrls());
        self::assertEquals('website', $creator->getWebsiteUrl());

        $creator
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

        self::assertEquals('', $creator->getLinklistUrl());
        self::assertEquals('faq1', $creator->getFaqUrl());
        self::assertEquals(['other'], $creator->getPhotoUrls());
        self::assertEquals(['price1', 'cost2', 'price3'], $creator->getPricesUrls());
        self::assertEquals('websiteChanged', $creator->getWebsiteUrl());

        $creator
            ->setPhotoUrls([]) // Clear
        ;

        self::assertEquals([ // Other removed completely
            'URL_FAQ faq1',
            'URL_PRICES cost2',
            'URL_PRICES price1',
            'URL_PRICES price3',
            'URL_WEBSITE websiteChanged',
        ], $this->getUrlArray($entity));

        self::assertEquals([], $creator->getPhotoUrls());
    }

    /**
     * @return string[]
     */
    private function getUrlArray(CreatorE $creator): array
    {
        $result = Vec\map($creator->getUrls(), fn (CreatorUrl $url) => $url->getType().' '.$url->getUrl());
        sort($result);

        return $result;
    }
}
