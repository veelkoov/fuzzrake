<?php

declare(strict_types=1);

namespace App\Tests\Utils\Data\Tidying;

use App\Data\Tidying\CreatorChanges;
use App\Entity\Creator as CreatorE;
use App\Entity\CreatorOfferStatus;
use App\Entity\CreatorUrl;
use App\Entity\CreatorValue;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;

/**
 * @medium
 */
class CreatorChangesWithEMTest extends FuzzrakeKernelTestCase
{
    public function testApply(): void
    {
        self::bootKernel();

        $creator1 = (new Creator())
            ->setName('Creator 1')
            ->setCity('Varkaus')
            ->setFaqUrl('https://some-faq-address/')
            ->setCommissionsUrls(['https://some-commissions-address/'])
            ->setOpenFor(['Pancakes'])
            ->setClosedFor(['Maple syrup'])
            ->setNsfwWebsite(true)
            ->setNsfwSocial(false)
        ;

        $creator2 = (new Creator())
            ->setName('Creator 2')
            ->setCity('Joensuu')
            ->setWebsiteUrl('https://some-website-address/')
            ->setTwitterUrl('https://some-twitter-address/')
            ->setOpenFor(['Cheese', 'Sitruuna'])
            ->setNsfwWebsite(false)
            ->setNsfwSocial(true)
        ;

        self::persistAndFlush($creator1, $creator2);

        $id1 = $creator1->getId();
        $id2 = $creator2->getId();

        $changes1 = new CreatorChanges($creator1);
        $changes2 = new CreatorChanges($creator2);

        $changes1->getChanged()
            ->setCity('Tampere')
            ->setFaqUrl('')
            ->setEtsyUrl('https://some-etsy-address/')
            ->setOpenFor(['Popcorn'])
            ->setClosedFor(['Maple syrup', 'Tortillas'])
            ->setNsfwWebsite(false)
            ->setNsfwSocial(true)
        ;
        $changes2->getChanged()
            ->setCity('Kouvola')
            ->setWebsiteUrl('')
            ->setFurryAminoUrl('https://some-furry-amino-address/')
            ->setOpenFor(['Sitruuna', 'Omena'])
            ->setNsfwWebsite(true)
            ->setNsfwSocial(false)
        ;

        $changes1->apply();
        // Deliberately SKIP applying changes to creator 2

        $em = self::getEM();
        $em->flush();
        $em->clear();

        unset($creator1, $creator2);

        $creator1 = $em->find(CreatorE::class, $id1);
        $creator2 = $em->find(CreatorE::class, $id2);

        self::assertNotNull($creator1);
        self::assertNotNull($creator2);

        self::assertEquals('Creator 1', $creator1->getName());
        self::assertEquals('Tampere', $creator1->getCity());

        self::assertEquals('Creator 2', $creator2->getName());
        self::assertEquals('Joensuu', $creator2->getCity());

        $urls1 = [];
        $urls2 = [];
        $comms1 = [];
        $comms2 = [];
        $vals1 = [];
        $vals2 = [];

        foreach ($creator1->getUrls()->toArray() as /* @var $url CreatorUrl */ $url) {
            $urls1[$url->getType()] = $url->getUrl();
        }
        foreach ($creator2->getUrls()->toArray() as /* @var $url CreatorUrl */ $url) {
            $urls2[$url->getType()] = $url->getUrl();
        }
        foreach ($creator1->getOfferStatuses()->toArray() as /* @var $status CreatorOfferStatus */ $status) {
            $comms1[$status->getOffer()] = $status->getIsOpen();
        }
        foreach ($creator2->getOfferStatuses()->toArray() as /* @var $status CreatorOfferStatus */ $status) {
            $comms2[$status->getOffer()] = $status->getIsOpen();
        }
        foreach ($creator1->getValues()->toArray() as /* @var $value CreatorValue */ $value) {
            $vals1[$value->getFieldName()] = $value->getValue();
        }
        foreach ($creator2->getValues()->toArray() as /* @var $value CreatorValue */ $value) {
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

        self::assertEquals(['NSFW_WEBSITE' => 'False', 'NSFW_SOCIAL' => 'True'], $vals1);
        self::assertEquals(['NSFW_WEBSITE' => 'False', 'NSFW_SOCIAL' => 'True'], $vals2);
    }
}
