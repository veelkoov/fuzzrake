<?php

declare(strict_types=1);

namespace App\Tests\Event\Doctrine;

use App\Entity\Artisan as ArtisanE;
use App\Entity\ArtisanUrl;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

/**
 * @medium
 */
class ArtisanUrlListenerTest extends KernelTestCaseWithEM
{
    /**
     * @throws Exception
     */
    public function testChangingUrlResetsLastSuccessAndFailure(): void
    {
        self::bootKernel();

        $lastFailureUtc = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $lastSuccessUtc = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $lastFailureCode = 404;
        $lastFailureReason = 'test reason';

        $persistedArtisan = new ArtisanE();
        $artisanUrl = new ArtisanUrl();
        $artisanUrl->getState()
            ->setLastFailureUtc($lastFailureUtc)
            ->setLastSuccessUtc($lastSuccessUtc)
            ->setLastFailureCode($lastFailureCode)
            ->setLastFailureReason($lastFailureReason);
        $persistedArtisan->addUrl($artisanUrl);

        self::persistAndFlush($persistedArtisan);

        /** @var ArtisanE $retrievedArtisan */
        $retrievedArtisan = self::getEM()->getRepository(ArtisanE::class)->findAll()[0];
        $url = $retrievedArtisan->getUrls()[0];
        self::assertNotNull($url);

        self::assertEquals($lastSuccessUtc, $url->getState()->getLastSuccessUtc());
        self::assertEquals($lastFailureUtc, $url->getState()->getLastFailureUtc());
        self::assertEquals($lastFailureCode, $url->getState()->getLastFailureCode());
        self::assertEquals($lastFailureReason, $url->getState()->getLastFailureReason());

        $url->setUrl('new url');

        self::flush();

        $retrievedArtisan = self::getEM()->getRepository(ArtisanE::class)->findAll()[0];
        $url = $retrievedArtisan->getUrls()[0];
        self::assertNotNull($url);

        self::assertNull($url->getState()->getLastSuccessUtc());
        self::assertNull($url->getState()->getLastFailureUtc());
        self::assertEquals(0, $url->getState()->getLastFailureCode());
        self::assertEmpty($url->getState()->getLastFailureReason());
    }
}
