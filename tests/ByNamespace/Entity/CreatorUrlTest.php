<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Entity;

use App\Entity\Creator;
use App\Entity\CreatorUrl;
use App\Entity\User;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Tests\TestUtils\UserCreator;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
#[CoversClass(CreatorUrl::class)]
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

        $persistedCreator = new Creator($user = new User());
        $persistedCreatorUrl = new CreatorUrl();
        $persistedCreatorUrl->getState()
            ->setLastFailureUtc($lastFailureUtc)
            ->setLastSuccessUtc($lastSuccessUtc)
            ->setLastFailureCode($lastFailureCode)
            ->setLastFailureReason($lastFailureReason);
        $persistedCreator->addUrl($persistedCreatorUrl);

        self::persistAndFlush($persistedCreator, $user);

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
        $persistedCreator = UserCreator::get();
        $persistedCreatorUrl = new CreatorUrl();
        $persistedCreator->entity->addUrl($persistedCreatorUrl);

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
