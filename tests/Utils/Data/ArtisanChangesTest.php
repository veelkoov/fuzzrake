<?php

declare(strict_types=1);

namespace App\Tests\Utils\Data;

use App\Entity\Artisan as ArtisanE;
use App\Entity\ArtisanCommissionsStatus;
use App\Entity\ArtisanUrl;
use App\Entity\ArtisanValue;
use App\Tests\TestUtils\DbEnabledKernelTestCase;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\ArtisanChanges;

class ArtisanChangesTest extends DbEnabledKernelTestCase
{
    public function testApply(): void
    {
        self::bootKernel();

        $artisan1 = (new Artisan())
            ->setName('Artisan 1')
            ->setCity('Varkaus')
            ->setFaqUrl('https://some-faq-address/')
            ->setCommissionsUrls('https://some-commissions-address/')
            ->setOpenFor('Pancakes')
            ->setClosedFor('Maple syrup')
            ->setIsMinor(true)
            ->setWorksWithMinors(false)
        ;

        $artisan2 = (new Artisan())
            ->setName('Artisan 2')
            ->setCity('Joensuu')
            ->setWebsiteUrl('https://some-website-address/')
            ->setTwitterUrl('https://some-twitter-address/')
            ->setOpenFor("Cheese\nSitruuna")
            ->setIsMinor(false)
            ->setWorksWithMinors(true)
        ;

        self::persistAndFlush($artisan1, $artisan2);

        $id1 = $artisan1->getId();
        $id2 = $artisan2->getId();

        $changes1 = new ArtisanChanges($artisan1);
        $changes2 = new ArtisanChanges($artisan2);

        $changes1->getChanged()
            ->setCity('Tampere')
            ->setFaqUrl('')
            ->setEtsyUrl('https://some-etsy-address/')
            ->setOpenFor('Popcorn')
            ->setClosedFor("Maple syrup\nTortillas")
            ->setIsMinor(false)
            ->setWorksWithMinors(true)
        ;
        $changes2->getChanged()
            ->setCity('Kouvola')
            ->setWebsiteUrl('')
            ->setFurryAminoUrl('https://some-furry-amino-address/')
            ->setOpenFor("Sitruuna\nOmena")
            ->setIsMinor(true)
            ->setWorksWithMinors(false)
        ;

        $changes1->apply();
        // Deliberately SKIP applying changes to artisan 2

        $em = self::getEM();
        $em->flush();
        $em->clear();

        unset($artisan1, $artisan2);

        $artisan1 = $em->find(ArtisanE::class, $id1);
        $artisan2 = $em->find(ArtisanE::class, $id2);

        self::assertEquals('Artisan 1', $artisan1->getName());
        self::assertEquals('Tampere', $artisan1->getCity());

        self::assertEquals('Artisan 2', $artisan2->getName());
        self::assertEquals('Joensuu', $artisan2->getCity());

        $urls1 = [];
        $urls2 = [];
        $comms1 = [];
        $comms2 = [];
        $vals1 = [];
        $vals2 = [];

        foreach ($artisan1->getUrls()->toArray() as /* @var $url ArtisanUrl */ $url) {
            $urls1[$url->getType()] = $url->getUrl();
        }
        foreach ($artisan2->getUrls()->toArray() as /* @var $url ArtisanUrl */ $url) {
            $urls2[$url->getType()] = $url->getUrl();
        }
        foreach ($artisan1->getCommissions()->toArray() as /* @var $status ArtisanCommissionsStatus */ $status) {
            $comms1[$status->getOffer()] = $status->getIsOpen();
        }
        foreach ($artisan2->getCommissions()->toArray() as /* @var $status ArtisanCommissionsStatus */ $status) {
            $comms2[$status->getOffer()] = $status->getIsOpen();
        }
        foreach ($artisan1->getValues()->toArray() as /* @var $value ArtisanValue */ $value) {
            $vals1[$value->getFieldName()] = $value->getValue();
        }
        foreach ($artisan2->getValues()->toArray() as /* @var $value ArtisanValue */ $value) {
            $vals2[$value->getFieldName()] = $value->getValue();
        }

        self::assertEquals([
            'URL_COMMISSIONS' => 'https://some-commissions-address/',
            'URL_ETSY'        => 'https://some-etsy-address/',
        ], array_filter($urls1));
        self::assertEquals([
            'URL_WEBSITE' => 'https://some-website-address/',
            'URL_TWITTER' => 'https://some-twitter-address/',
        ], array_filter($urls2));

        self::assertEquals(['Maple syrup' => false, 'Tortillas' => false, 'Popcorn' => true], $comms1);
        self::assertEquals(['Cheese' => true, 'Sitruuna' => true], $comms2);

        self::assertEquals(['IS_MINOR' => 'False', 'WORKS_WITH_MINORS' => 'True'], $vals1);
        self::assertEquals(['IS_MINOR' => 'False', 'WORKS_WITH_MINORS' => 'True'], $vals2);
    }
}
