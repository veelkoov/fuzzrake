<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Listeners;

use App\Entity\Artisan;
use App\Entity\ArtisanUrl;
use App\Tests\TestUtils\SchemaTool;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ArtisanUrlListenerTest extends KernelTestCase
{
    public function testChangingUrlResetsLastSuccessAndFailure(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $em */
        $em = self::$container->get('doctrine.orm.default_entity_manager');
        SchemaTool::resetOn($em);

        $lastFailure = new DateTime();
        $lastSuccess = new DateTime();
        $lastFailureCode = 404;
        $lastFailureReason = 'test reason';

        $persistedArtisan = new Artisan();
        $persistedArtisan->addUrl((new ArtisanUrl())->getState()->setLastFailure($lastFailure)->setLastSuccess($lastSuccess)->setLastFailureCode($lastFailureCode)->setLastFailureReason($lastFailureReason)->getUrl());

        $em->persist($persistedArtisan);
        $em->flush();

        /** @var Artisan $retrievedArtisan */
        $retrievedArtisan = $em->getRepository(Artisan::class)->findAll()[0];
        $url = $retrievedArtisan->getUrls()[0];

        self::assertEquals($lastSuccess, $url->getState()->getLastSuccess());
        self::assertEquals($lastFailure, $url->getState()->getLastFailure());
        self::assertEquals($lastFailureCode, $url->getState()->getLastFailureCode());
        self::assertEquals($lastFailureReason, $url->getState()->getLastFailureReason());

        $url->setUrl('new url');

        $em->flush();

        $retrievedArtisan = $em->getRepository(Artisan::class)->findAll()[0];
        $url = $retrievedArtisan->getUrls()[0];

        self::assertNull($url->getState()->getLastSuccess());
        self::assertNull($url->getState()->getLastFailure());
        self::assertEquals(0, $url->getState()->getLastFailureCode());
        self::assertEmpty($url->getState()->getLastFailureReason());
    }
}
