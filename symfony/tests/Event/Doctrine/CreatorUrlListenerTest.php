<?php

declare(strict_types=1);

namespace App\Tests\Event\Doctrine;

use App\Entity\Creator as CreatorE;
use App\Entity\CreatorUrl;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

/**
 * @medium
 */
class CreatorUrlListenerTest extends FuzzrakeKernelTestCase
{
    /**
     * @throws Exception
     */
    public function testChangingUrlResetsLastSuccessAndFailure(): void
    {
        $lastFailureUtc = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $lastSuccessUtc = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $lastFailureCode = 404;
        $lastFailureReason = 'test reason';

        $persistedCreator = new CreatorE();
        $creatorUrl = new CreatorUrl();
        $creatorUrl->getState()
            ->setLastFailureUtc($lastFailureUtc)
            ->setLastSuccessUtc($lastSuccessUtc)
            ->setLastFailureCode($lastFailureCode)
            ->setLastFailureReason($lastFailureReason);
        $persistedCreator->addUrl($creatorUrl);

        self::persistAndFlush($persistedCreator);

        /** @var CreatorE $retrievedCreator */
        $retrievedCreator = self::getEM()->getRepository(CreatorE::class)->findAll()[0];
        $url = $retrievedCreator->getUrls()[0];
        self::assertNotNull($url);

        self::assertEquals($lastSuccessUtc, $url->getState()->getLastSuccessUtc());
        self::assertEquals($lastFailureUtc, $url->getState()->getLastFailureUtc());
        self::assertSame($lastFailureCode, $url->getState()->getLastFailureCode());
        self::assertSame($lastFailureReason, $url->getState()->getLastFailureReason());

        $url->setUrl('new url');

        self::flush();

        $retrievedCreator = self::getEM()->getRepository(CreatorE::class)->findAll()[0];
        $url = $retrievedCreator->getUrls()[0];
        self::assertNotNull($url);

        self::assertNull($url->getState()->getLastSuccessUtc());
        self::assertNull($url->getState()->getLastFailureUtc());
        self::assertSame(0, $url->getState()->getLastFailureCode());
        self::assertEmpty($url->getState()->getLastFailureReason());
    }
}
