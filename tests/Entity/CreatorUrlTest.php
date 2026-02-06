<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Creator;
use App\Entity\CreatorUrl;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class CreatorUrlTest extends FuzzrakeKernelTestCase
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

        $persistedCreator = new Creator();
        $persistedCreatorUrl = new CreatorUrl();
        $persistedCreatorUrl->getState()
            ->setLastFailureUtc($lastFailureUtc)
            ->setLastSuccessUtc($lastSuccessUtc)
            ->setLastFailureCode($lastFailureCode)
            ->setLastFailureReason($lastFailureReason);
        $persistedCreator->addUrl($persistedCreatorUrl);

        self::persistAndFlush($persistedCreator);

        /** @var Creator $retrievedCreator */
        $retrievedCreator = self::getEM()->getRepository(Creator::class)->findAll()[0];
        $retrievedUrl = $retrievedCreator->getUrls()[0];
        self::assertNotNull($retrievedUrl);

        self::assertEquals($lastSuccessUtc, $retrievedUrl->getState()->getLastSuccessUtc());
        self::assertEquals($lastFailureUtc, $retrievedUrl->getState()->getLastFailureUtc());
        self::assertSame($lastFailureCode, $retrievedUrl->getState()->getLastFailureCode());
        self::assertSame($lastFailureReason, $retrievedUrl->getState()->getLastFailureReason());

        $retrievedUrl->setUrl('new url');

        self::flush();

        $retrievedCreator = self::getEM()->getRepository(Creator::class)->findAll()[0];
        $retrievedUrl = $retrievedCreator->getUrls()[0];
        self::assertNotNull($retrievedUrl);

        self::assertNull($retrievedUrl->getState()->getLastSuccessUtc());
    }

    /**
     * @throws Exception
     */
    public function testChangingUrlDoesNotCreateStateIfWasMissing(): void
    {
        $persistedCreator = new Creator();
        $persistedCreatorUrl = new CreatorUrl();
        $persistedCreator->addUrl($persistedCreatorUrl);

        self::persistAndFlush($persistedCreator);

        /** @var Creator $retrievedCreator */
        $retrievedCreator = self::getEM()->getRepository(Creator::class)->findAll()[0];
        $retrievedUrl = $retrievedCreator->getUrls()[0];
        self::assertNotNull($retrievedUrl);

        $retrievedUrl->setUrl('new url');

        self::flush();

        $retrievedCreator = self::getEM()->getRepository(Creator::class)->findAll()[0];
        $retrievedUrl = $retrievedCreator->getUrls()[0];
        self::assertNotNull($retrievedUrl);

        self::assertNull($retrievedUrl->getState()->getId(), 'A state should not have been created.');
    }
}
