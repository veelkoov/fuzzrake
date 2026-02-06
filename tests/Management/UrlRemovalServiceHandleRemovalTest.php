<?php

declare(strict_types=1);

namespace App\Tests\Management;

use App\Data\Definitions\Fields\Field;
use App\Management\UrlRemovalService;
use App\Service\EmailService;
use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Mx\CreatorUrlsRemovalData;
use App\Utils\Mx\GroupedUrl;
use App\Utils\Mx\GroupedUrls;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\RouterInterface;

#[Small]
class UrlRemovalServiceHandleRemovalTest extends FuzzrakeTestCase
{
    use ClockSensitiveTrait;

    private UrlRemovalService $subject;
    private EmailService&MockObject $emailServiceMock;

    #[Override]
    protected function setUp(): void
    {
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())->method('flush');

        $routerMock = $this->createMock(RouterInterface::class);
        $routerMock->expects(self::atMost(3))->method('generate')
            ->willReturnOnConsecutiveCalls('/#TEST001', '/ui/update', '/contact');

        $this->emailServiceMock = $this->createMock(EmailService::class);

        $this->subject = new UrlRemovalService($entityManagerMock, 'ShortWebsiteName',
            'https://website.base.address.example.com', $routerMock, $this->emailServiceMock);

        self::mockTime();
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testEmptyNotesGetSet(): void
    {
        $creator = new Creator();

        $removedUrls = new GroupedUrls([
            new GroupedUrl(Field::URL_FAQ, 0, 'https://example.com/'),
            new GroupedUrl(Field::URL_PRICES, 1, 'http://example.net/'),
        ]);
        $data = new CreatorUrlsRemovalData($removedUrls, new GroupedUrls([]), false, false);

        $this->subject->handleRemoval($creator, $data);

        $dateTime = UtcClock::now()->format('Y-m-d H:i');
        self::assertEquals(<<<EXPECTED
            On $dateTime UTC the following links have been found to no longer work or to be inactive and have been removed:
            - https://example.com/
            - http://example.net/
            EXPECTED, $creator->getNotes());
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testNonEmptyNotesGetUpdated(): void
    {
        $creator = new Creator()->setNotes("  Some previous stuff\nBlah blah\n");

        $removedUrls = new GroupedUrls([
            new GroupedUrl(Field::URL_FAQ, 0, 'https://example.com/'),
        ]);
        $data = new CreatorUrlsRemovalData($removedUrls, new GroupedUrls([]), false, false);

        $this->subject->handleRemoval($creator, $data);

        $dateTime = UtcClock::now()->format('Y-m-d H:i');
        self::assertEquals(<<<EXPECTED
            On $dateTime UTC the following links have been found to no longer work or to be inactive and have been removed:
            - https://example.com/

            -----
            Some previous stuff
            Blah blah
            EXPECTED, $creator->getNotes());
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testCreatorGettingHiddenWhenDesired(): void
    {
        $creator = new Creator();
        $data = new CreatorUrlsRemovalData(new GroupedUrls([]), new GroupedUrls([]), true, false);

        $this->subject->handleRemoval($creator, $data);

        self::assertSame('All previously known websites/social accounts are no longer working or are inactive',
            $creator->getInactiveReason());
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testCreatorNotGettingHiddenWhenNotDesired(): void
    {
        $creator = new Creator();
        $data = new CreatorUrlsRemovalData(new GroupedUrls([]), new GroupedUrls([]), false, false);

        $this->subject->handleRemoval($creator, $data);

        self::assertSame('', $creator->getInactiveReason());
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testUrlsAreUpdatedAsDesired(): void
    {
        $creator = new Creator()
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

        self::assertSame('https://getfursu.it/info', $creator->getWebsiteUrl());
        self::assertEquals(['https://example.com/prices0'], $creator->getPricesUrls());
        self::assertEquals(['https://example.com/commissions1'], $creator->getCommissionsUrls());
        self::assertSame('', $creator->getFaqUrl());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testMessageNotSentWhenNotDesired(): void
    {
        $this->emailServiceMock->expects(self::never())->method('send');

        $creator = new Creator();
        $data = new CreatorUrlsRemovalData(new GroupedUrls([]), new GroupedUrls([]), false, false);

        $this->subject->handleRemoval($creator, $data);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testProperHidingMessageSentWhenDesired(): void
    {
        $this->emailServiceMock->expects(self::once())->method('send')->willReturnCallback(
            function (string $subject, string $contents, string $recipient, string $attachedJsonData): void {
                self::assertSame('Your card at ShortWebsiteName has been hidden', $subject);
                self::assertSame(<<<'CONTENTS'
                    Hello The Hidden Creator!

                    Your information at ShortWebsiteName ( https://website.base.address.example.com/#TEST001 ) may require your attention. All the links provided previously were found to be either no longer working, or to lead to inactive social accounts, and so have been removed:
                    - https://getfursu.it/info

                    Since the remaining information+links on your card are not sufficient, your card has been hidden.

                    Feel free to send new links (and restore your card's visibility) and update any other information at any time by using the following form:
                    https://website.base.address.example.com/ui/update

                    If you have any questions or need help with ShortWebsiteName, please do not hesitate to initiate contact using any means listed on this page:
                    https://website.base.address.example.com/contact
                    CONTENTS, $contents);
                self::assertSame('', $recipient);
                self::assertSame('', $attachedJsonData);
            });

        $creator = new Creator()->setName('The Hidden Creator')->setCreatorId('TEST001');

        $removedUrls = new GroupedUrls([
            new GroupedUrl(Field::URL_WEBSITE, 0, 'https://getfursu.it/info'),
        ]);

        $data = new CreatorUrlsRemovalData($removedUrls, new GroupedUrls([]), true, true);

        $this->subject->handleRemoval($creator, $data);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testProperUrlRemovalMessageSentWhenDesired(): void
    {
        $this->emailServiceMock->expects(self::once())->method('send')->willReturnCallback(
            function (string $subject, string $contents, string $recipient, string $attachedJsonData): void {
                self::assertSame('Your information at ShortWebsiteName may require your attention', $subject);
                self::assertSame(<<<'CONTENTS'
                    Hello The Updated Creator!

                    Your information at ShortWebsiteName ( https://website.base.address.example.com/#TEST001 ) may require your attention. The following links were found to be either no longer working, or to lead to inactive social accounts, and so have been removed:
                    - https://getfursu.it/info
                    - https://example.com/prices
                    - https://example.com/commissions

                    Feel free to send new links or update any other information at any time by using the following form:
                    https://website.base.address.example.com/ui/update

                    If you have any questions or need help with ShortWebsiteName, please do not hesitate to initiate contact using any means listed on this page:
                    https://website.base.address.example.com/contact
                    CONTENTS, $contents);
                self::assertSame('', $recipient);
                self::assertSame('', $attachedJsonData);
            });

        $creator = new Creator()->setName('The Updated Creator')->setCreatorId('TEST001');

        $removedUrls = new GroupedUrls([
            new GroupedUrl(Field::URL_WEBSITE, 0, 'https://getfursu.it/info'),
            new GroupedUrl(Field::URL_PRICES, 0, 'https://example.com/prices'),
            new GroupedUrl(Field::URL_COMMISSIONS, 1, 'https://example.com/commissions'),
        ]);

        $data = new CreatorUrlsRemovalData($removedUrls, new GroupedUrls([]), false, true);

        $this->subject->handleRemoval($creator, $data);
    }
}
