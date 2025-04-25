<?php

declare(strict_types=1);

namespace App\Tests\Management;

use App\Data\Definitions\Fields\Field;
use App\Management\UrlRemovalService;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Mx\CreatorUrlsRemovalData;
use App\Utils\Mx\GroupedUrl;
use App\Utils\Mx\GroupedUrls;
use App\Utils\TestUtils\TestsBridge;
use App\Utils\TestUtils\UtcClockMock;
use App\ValueObject\Messages\EmailNotificationV1;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @small
 */
class UrlRemovalServiceHandleRemovalTest extends TestCase
{
    private UrlRemovalService $subject;
    private DateTimeInterface $now;
    private MessageBusInterface&MockObject $messengerBusMock;

    #[Override]
    protected function setUp(): void
    {
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects($this->once())->method('flush');

        $routerMock = $this->createMock(RouterInterface::class);
        $routerMock->expects($this->atMost(3))->method('generate')
            ->willReturnOnConsecutiveCalls('/#TEST001', '/ui/update', '/contact');

        $this->messengerBusMock = $this->createMock(MessageBusInterface::class);

        $this->subject = new UrlRemovalService($entityManagerMock, 'ShortWebsiteName',
            'https://website.base.address.example.com', $routerMock, $this->messengerBusMock);

        UtcClockMock::start();
        $this->now = UtcClock::now();
    }

    #[Override]
    protected function tearDown(): void
    {
        TestsBridge::reset();
    }

    /**
     * @throws ExceptionInterface
     */
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
            On {$this->now->format('Y-m-d H:i')} UTC the following links have been found to no longer work or to be inactive and have been removed:
            - https://example.com/
            - http://example.net/
            EXPECTED, $creator->getNotes());
    }

    /**
     * @throws ExceptionInterface
     */
    public function testNonEmptyNotesGetUpdated(): void
    {
        $creator = Creator::new()->setNotes("  Some previous stuff\nBlah blah\n");

        $removedUrls = new GroupedUrls([
            new GroupedUrl(Field::URL_FAQ, 0, 'https://example.com/'),
        ]);
        $data = new CreatorUrlsRemovalData($removedUrls, new GroupedUrls([]), false, false);

        $this->subject->handleRemoval($creator, $data);

        self::assertEquals(<<<EXPECTED
            On {$this->now->format('Y-m-d H:i')} UTC the following links have been found to no longer work or to be inactive and have been removed:
            - https://example.com/

            -----
            Some previous stuff
            Blah blah
            EXPECTED, $creator->getNotes());
    }

    /**
     * @throws ExceptionInterface
     */
    public function testCreatorGettingHiddenWhenDesired(): void
    {
        $creator = new Creator();
        $data = new CreatorUrlsRemovalData(new GroupedUrls([]), new GroupedUrls([]), true, false);

        $this->subject->handleRemoval($creator, $data);

        self::assertEquals('All previously known websites/social accounts are no longer working or are inactive',
            $creator->getInactiveReason());
    }

    /**
     * @throws ExceptionInterface
     */
    public function testCreatorNotGettingHiddenWhenNotDesired(): void
    {
        $creator = new Creator();
        $data = new CreatorUrlsRemovalData(new GroupedUrls([]), new GroupedUrls([]), false, false);

        $this->subject->handleRemoval($creator, $data);

        self::assertEquals('', $creator->getInactiveReason());
    }

    /**
     * @throws ExceptionInterface
     */
    public function testUrlsAreUpdatedAsDesired(): void
    {
        $creator = Creator::new()
            ->setWebsiteUrl('https://getfursu.it/info')
            ->setPricesUrls([
                'https://example.com/prices0',
                'http://example.net/prices1',
            ])
            ->setCommissionsUrls([
                'http://example.net/commissions0',
                'https://example.com/commissions1',
            ])
            ->setFaqUrl('http://getfursu.it/faq')
        ;

        $remainingUrls = new GroupedUrls([
            new GroupedUrl(Field::URL_WEBSITE, 0, 'https://getfursu.it/info'),
            new GroupedUrl(Field::URL_PRICES, 0, 'https://example.com/prices0'),
            new GroupedUrl(Field::URL_COMMISSIONS, 1, 'https://example.com/commissions1'),
        ]);

        $data = new CreatorUrlsRemovalData(new GroupedUrls([]), $remainingUrls, false, false);

        $this->subject->handleRemoval($creator, $data);

        self::assertEquals('https://getfursu.it/info', $creator->getWebsiteUrl());
        self::assertEquals(['https://example.com/prices0'], $creator->getPricesUrls());
        self::assertEquals(['https://example.com/commissions1'], $creator->getCommissionsUrls());
        self::assertEquals('', $creator->getFaqUrl());
    }

    /**
     * @throws ExceptionInterface
     */
    public function testMessageNotSentWhenNotDesired(): void
    {
        $this->messengerBusMock->expects($this->never())->method('dispatch');

        $creator = new Creator();
        $data = new CreatorUrlsRemovalData(new GroupedUrls([]), new GroupedUrls([]), false, false);

        $this->subject->handleRemoval($creator, $data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testProperHidingMessageSentWhenDesired(): void
    {
        $this->messengerBusMock->expects($this->once())->method('dispatch')->willReturnCallback(
            function (EmailNotificationV1 $notification): Envelope {
                $this->assertEquals('Your card at ShortWebsiteName has been hidden', $notification->subject);
                $this->assertEquals(<<<'CONTENTS'
                    Hello The Hidden Creator!

                    Your information at ShortWebsiteName ( https://website.base.address.example.com/#TEST001 ) may require your attention. All the links provided previously were found to be either no longer working, or to lead to inactive social accounts, and so have been removed:
                    - https://getfursu.it/info

                    Since the remaining information+links on your card are not sufficient, your card has been hidden.

                    Feel free to send new links (and restore your card's visibility) and update any other information at any time by using the following form:
                    https://website.base.address.example.com/ui/update

                    If you have any questions or need help with ShortWebsiteName, please do not hesitate to initiate contact using any means listed on this page:
                    https://website.base.address.example.com/contact
                    CONTENTS, $notification->contents);

                return new Envelope($notification);
            });

        $creator = Creator::new()->setName('The Hidden Creator')->setCreatorId('TEST001');

        $removedUrls = new GroupedUrls([
            new GroupedUrl(Field::URL_WEBSITE, 0, 'https://getfursu.it/info'),
        ]);

        $data = new CreatorUrlsRemovalData($removedUrls, new GroupedUrls([]), true, true);

        $this->subject->handleRemoval($creator, $data);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testProperUrlRemovalMessageSentWhenDesired(): void
    {
        $this->messengerBusMock->expects($this->once())->method('dispatch')->willReturnCallback(
            function (EmailNotificationV1 $notification): Envelope {
                $this->assertEquals('Your information at ShortWebsiteName may require your attention', $notification->subject);
                $this->assertEquals(<<<'CONTENTS'
                    Hello The Updated Creator!

                    Your information at ShortWebsiteName ( https://website.base.address.example.com/#TEST001 ) may require your attention. The following links were found to be either no longer working, or to lead to inactive social accounts, and so have been removed:
                    - https://getfursu.it/info
                    - https://example.com/prices
                    - https://example.com/commissions

                    Feel free to send new links or update any other information at any time by using the following form:
                    https://website.base.address.example.com/ui/update

                    If you have any questions or need help with ShortWebsiteName, please do not hesitate to initiate contact using any means listed on this page:
                    https://website.base.address.example.com/contact
                    CONTENTS, $notification->contents);

                return new Envelope($notification);
            });

        $creator = Creator::new()->setName('The Updated Creator')->setCreatorId('TEST001');

        $removedUrls = new GroupedUrls([
            new GroupedUrl(Field::URL_WEBSITE, 0, 'https://getfursu.it/info'),
            new GroupedUrl(Field::URL_PRICES, 0, 'https://example.com/prices'),
            new GroupedUrl(Field::URL_COMMISSIONS, 1, 'https://example.com/commissions'),
        ]);

        $data = new CreatorUrlsRemovalData($removedUrls, new GroupedUrls([]), false, true);

        $this->subject->handleRemoval($creator, $data);
    }
}
