<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Listeners;

use App\Entity\Artisan;
use App\Entity\ArtisanUrl;
use App\Tests\TestUtils\DbEnabledKernelTestCase;
use DateTime;

class ArtisanUrlListenerTest extends DbEnabledKernelTestCase
{
    public function testChangingUrlResetsLastSuccessAndFailure(): void
    {
        self::bootKernel();

        $lastFailure = new DateTime();
        $lastSuccess = new DateTime();
        $lastFailureCode = 404;
        $lastFailureReason = 'test reason';

        $persistedArtisan = new Artisan();
        $persistedArtisan->addUrl((new ArtisanUrl())->getState()->setLastFailure($lastFailure)->setLastSuccess($lastSuccess)->setLastFailureCode($lastFailureCode)->setLastFailureReason($lastFailureReason)->getUrl());

        self::getEM()->persist($persistedArtisan);
        self::getEM()->flush();

        /** @var Artisan $retrievedArtisan */
        $retrievedArtisan = self::getEM()->getRepository(Artisan::class)->findAll()[0];
        $url = $retrievedArtisan->getUrls()[0];

        self::assertEquals($lastSuccess, $url->getState()->getLastSuccess());
        self::assertEquals($lastFailure, $url->getState()->getLastFailure());
        self::assertEquals($lastFailureCode, $url->getState()->getLastFailureCode());
        self::assertEquals($lastFailureReason, $url->getState()->getLastFailureReason());

        $url->setUrl('new url');

        self::getEM()->flush();

        $retrievedArtisan = self::getEM()->getRepository(Artisan::class)->findAll()[0];
        $url = $retrievedArtisan->getUrls()[0];

        self::assertNull($url->getState()->getLastSuccess());
        self::assertNull($url->getState()->getLastFailure());
        self::assertEquals(0, $url->getState()->getLastFailureCode());
        self::assertEmpty($url->getState()->getLastFailureReason());
    }
}
