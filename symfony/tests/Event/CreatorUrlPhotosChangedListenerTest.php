<?php

declare(strict_types=1);

namespace App\Tests\Event;

use App\Data\Definitions\Fields\Field;
use App\Entity\Creator;
use App\Entity\CreatorUrl;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\ValueObject\Messages\UpdateMiniaturesV1;
use Doctrine\DBAL\Exception;
use RuntimeException;
use Zenstruck\Messenger\Test\InteractsWithMessenger;
use Zenstruck\Messenger\Test\Transport\TransportEnvelopeCollection;

class CreatorUrlPhotosChangedListenerTest extends FuzzrakeKernelTestCase
{
    use InteractsWithMessenger;

    public function testUpdateMessageGetsSentAfterAddingPhotoUrl(): void
    {
        $creatorUrl = (new CreatorUrl())
            ->setUrl('https://example.com/')
            ->setType(Field::URL_PHOTOS->value)
        ;
        $creator = (new Creator())->addUrl($creatorUrl);

        self::persistAndFlush($creator);
        $creatorId = $creator->getId();
        self::assertNotNull($creatorId);

        $this->assertSingleUpdateMiniaturesV1HasBeenSentFor($creatorId);
    }

    public function testUpdateMessageGetsSentAfterUpdatingPhotoUrl(): void
    {
        $creator = new Creator();
        self::persistAndFlush($creator);
        $creatorId = $creator->getId();
        self::assertNotNull($creatorId);

        $this->insertCreatorUrl($creatorId, Field::URL_PHOTOS->value, 'https://example.com/initial');
        self::clear();

        $creator = self::getCreatorRepository()->find($creatorId);
        self::assertNotNull($creator);
        $creatorUrl = $creator->getUrls()[0];
        self::assertNotNull($creatorUrl);
        $creatorUrl->setUrl('https://example.com/updated');

        self::flush();

        $this->assertSingleUpdateMiniaturesV1HasBeenSentFor($creatorId);
    }

    public function testUpdateMessageGetsSentAfterRemovingPhotoUrl(): void
    {
        $creator = new Creator();
        self::persistAndFlush($creator);
        $creatorId = $creator->getId();
        self::assertNotNull($creatorId);

        $this->insertCreatorUrl($creatorId, Field::URL_PHOTOS->value, 'https://example.com/');
        self::clear();

        $creator = self::getCreatorRepository()->find($creatorId);
        self::assertNotNull($creator);
        $creatorUrl = $creator->getUrls()[0];
        self::assertNotNull($creatorUrl);
        $creator->removeUrl($creatorUrl);

        self::flush();

        $this->assertSingleUpdateMiniaturesV1HasBeenSentFor($creatorId);
    }

    public function testNonPhotoUrlChangesAreIgnored(): void
    {
        $creatorUrl = (new CreatorUrl())
            ->setUrl('https://example.com/initial')
            ->setType(Field::URL_WEBSITE->value)
        ;
        $creator = (new Creator())->addUrl($creatorUrl);

        self::persistAndFlush($creator);

        $creatorUrl->setUrl('https://example.com/updated');

        self::flush();

        $this->assertNoUpdateMiniaturesV1HasBeenSent();
    }

    public function testMessagesAreNotDuplicated(): void
    {
        $creatorUrl1 = (new CreatorUrl())
            ->setUrl('https://example.com/url1')
            ->setType(Field::URL_PHOTOS->value)
        ;
        $creatorUrl2 = (new CreatorUrl())
            ->setUrl('https://example.com/url2')
            ->setType(Field::URL_PHOTOS->value)
        ;
        $creator = (new Creator())
            ->addUrl($creatorUrl1)
            ->addUrl($creatorUrl2)
        ;

        self::persistAndFlush($creator);
        $creatorId = $creator->getId();
        self::assertNotNull($creatorId);

        $this->assertSingleUpdateMiniaturesV1HasBeenSentFor($creatorId);
    }

    private function insertCreatorUrl(int $creatorId, string $type, string $url): void
    {
        try {
            self::getEM()->getConnection()->executeStatement('INSERT INTO creators_urls (creator_id, type, url) VALUES (:creator_id, :type, :url)', [
                'creator_id' => $creatorId,
                'type' => $type,
                'url' => $url,
            ]);
        } catch (Exception $exception) {
            throw new RuntimeException(previous: $exception);
        }
    }

    private function assertSingleUpdateMiniaturesV1HasBeenSentFor(int $creatorId): void
    {
        $this->getQueue()->assertContains(UpdateMiniaturesV1::class, 1);

        $message = $this->getQueue()->first()->getMessage();
        self::assertInstanceOf(UpdateMiniaturesV1::class, $message);
        self::assertSame($creatorId, $message->creatorId);
    }

    private function assertNoUpdateMiniaturesV1HasBeenSent(): void
    {
        $this->getQueue()->assertEmpty();
    }

    private function getQueue(): TransportEnvelopeCollection
    {
        return $this->transport('async-msg-queue')->queue();
    }
}
