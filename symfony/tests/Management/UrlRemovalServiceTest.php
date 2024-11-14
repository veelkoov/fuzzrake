<?php

namespace App\Tests\Management;

use App\Data\Definitions\Fields\Field;
use App\Management\UrlRemovalService;
use App\Service\Notifications\MessengerInterface;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Mx\CreatorUrlsRemovalData;
use App\Utils\Mx\GroupedUrl;
use App\Utils\Mx\GroupedUrls;
use App\Utils\TestUtils\TestsBridge;
use App\Utils\TestUtils\UtcClockMock;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class UrlRemovalServiceTest extends TestCase
{
    private UrlRemovalService $subject;
    private DateTimeInterface $now;
    private RouterInterface&MockObject $routerMock;

    protected function setUp(): void
    {
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects($this->once())->method('flush');

        $this->routerMock = $this->createMock(RouterInterface::class);

        $messengerMock = $this->createMock(MessengerInterface::class);

        $this->subject = new UrlRemovalService($entityManagerMock, 'Fuzzrake',
            'https://127.0.0.1', $this->routerMock, $messengerMock);

        UtcClockMock::start();
        $this->now = UtcClock::now();
    }

    // private function mockEmailBeingSent(): void
    // {
    //     $this->routerMock->expects($this->exactly(2))->method('generate')->willReturnOnConsecutiveCalls('/ui/update', '/contact');
    // }

    protected function tearDown(): void
    {
        TestsBridge::reset();
    }

    public function testEmptyNotesGetSet(): void
    {
        $creator = new Creator();

        $removedUrls = new GroupedUrls([
            new GroupedUrl(Field::URL_FAQ, 0, 'https://example.com/'),
            new GroupedUrl(Field::URL_PRICES, 1, 'http://example.net/'),
        ]);
        $data = new CreatorUrlsRemovalData($removedUrls, new GroupedUrls([]), false, false);

        $this->subject->handleRemoval($creator, $data);

        self::assertEquals(<<<EXPECTED
            On {$this->now->format('Y-m-d H:i')} UTC the following links were determined to be no longer working/active and have been removed:
            - https://example.com/
            - http://example.net/
            EXPECTED, $creator->getNotes());
    }

    public function testNonEmptyNotesGetUpdated(): void
    {
        $creator = Creator::new()->setNotes("  Some previous stuff\nBlah blah\n");

        $removedUrls = new GroupedUrls([
            new GroupedUrl(Field::URL_FAQ, 0, 'https://example.com/'),
        ]);
        $data = new CreatorUrlsRemovalData($removedUrls, new GroupedUrls([]), false, false);

        $this->subject->handleRemoval($creator, $data);

        self::assertEquals(<<<EXPECTED
            On {$this->now->format('Y-m-d H:i')} UTC the following links were determined to be no longer working/active and have been removed:
            - https://example.com/

            -----
            Some previous stuff
            Blah blah
            EXPECTED, $creator->getNotes());
    }
}
