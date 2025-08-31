<?php

declare(strict_types=1);

namespace App\Tests\Management;

use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Management\UrlRemovalService;
use App\Service\EmailService;
use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Mx\CreatorUrlsRemovalData;
use App\Utils\Mx\GroupedUrl;
use App\Utils\Mx\GroupedUrls;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Small;
use Symfony\Component\Routing\RouterInterface;
use Throwable;
use Veelkoov\Debris\Lists\StringList;

#[Small]
class UrlRemovalServiceGetRemovalDataForTest extends FuzzrakeTestCase
{
    public function testEmailNotSentWhenNoContactPermitted(): void
    {
        $creator = new Creator()->setContactAllowed(ContactPermit::NO)
            ->setWebsiteUrl('https://localhost/');

        $result = UrlRemovalService::getRemovalDataFor($creator, StringList::of('URL_WEBSITE_0'));

        self::assertFalse($result->sendEmail);
    }

    public function testEmailSentWhenContactPermitted(): void
    {
        $creator = new Creator()->setContactAllowed(ContactPermit::CORRECTIONS)
            ->setWebsiteUrl('https://localhost/');

        $result = UrlRemovalService::getRemovalDataFor($creator, StringList::of('URL_WEBSITE_0'));

        self::assertTrue($result->sendEmail);
    }

    public function testRemovalOfAllImportantLinksHides(): void
    {
        $creator = new Creator()
            ->setWebsiteUrl('https://localhost/')
            ->setCommissionsUrls(['https://localhost/'])
        ;

        $result = UrlRemovalService::getRemovalDataFor($creator, StringList::of('URL_WEBSITE_0'));

        self::assertTrue($result->hide);
    }

    public function testImportantLinksLeftDoNotHide(): void
    {
        $creator = new Creator()
            ->setWebsiteUrl('https://localhost/')
            ->setCommissionsUrls(['https://localhost/'])
        ;

        $result = UrlRemovalService::getRemovalDataFor($creator, StringList::of('URL_COMMISSIONS_0'));

        self::assertFalse($result->hide);
    }

    public function testNoRemovedUrlsThrowException(): void
    {
        self::expectException(InvalidArgumentException::class);
        UrlRemovalService::getRemovalDataFor(new Creator(), StringList::of());
    }

    public function testUnknownUrlIdThrowException(): void
    {
        $creator = new Creator()->setWebsiteUrl('https://localhost/');

        self::expectException(InvalidArgumentException::class);
        UrlRemovalService::getRemovalDataFor($creator, StringList::of('WRONG'));
    }

    public function testRemovedAndRemainingAreCalculatedProperly(): void
    {
        $creator = new Creator()
            ->setWebsiteUrl('https://localhost/main')
            ->setCommissionsUrls(['https://com1.example.com/', 'https://com2.example.com/'])
            ->setPricesUrls(['https://prc1.example.com/', 'https://prc2.example.com/'])
            ->setFaqUrl('https://localhost/faq')
        ;

        $result = UrlRemovalService::getRemovalDataFor($creator, StringList::of('URL_FAQ_0', 'URL_COMMISSIONS_1', 'URL_PRICES_0'));

        self::assertSameItems(
            ['https://localhost/faq', 'https://com2.example.com/', 'https://prc1.example.com/'],
            $result->removedUrls->mapInto(static fn (GroupedUrl $url) => $url->url, new StringList()),
        );

        self::assertSameItems(
            ['https://localhost/main', 'https://com1.example.com/', 'https://prc2.example.com/'],
            $result->remainingUrls->mapInto(static fn (GroupedUrl $url) => $url->url, new StringList()),
        );
    }

    /**
     * @throws Throwable
     */
    public function testHidingCreatorRemoves(): void
    {
        $creator = new Creator()
            ->setWebsiteUrl('https://localhost/main')
            ->setCommissionsUrls(['https://com1.example.com/', 'https://com2.example.com/'])
            ->setFaqUrl('https://localhost/faq')
        ;

        $input = new CreatorUrlsRemovalData(
            GroupedUrls::of(
                new GroupedUrl(Field::URL_WEBSITE, 0, 'https://localhost/main'),
            ),
            GroupedUrls::of(
                new GroupedUrl(Field::URL_COMMISSIONS, 0, 'https://com1.example.com/'),
                new GroupedUrl(Field::URL_COMMISSIONS, 1, 'https://com2.example.com/'),
                new GroupedUrl(Field::URL_FAQ, 0, 'https://localhost/faq'),
            ),
            true,
            false,
        );

        $subject = new UrlRemovalService(
            self::createStub(EntityManagerInterface::class),
            '',
            '',
            self::createStub(RouterInterface::class),
            self::createStub(EmailService::class),
        );

        $subject->handleRemoval($creator, $input);

        self::assertEmpty($creator->getCommissionsUrls());
        self::assertStringContainsString('https://com1.example.com/', $creator->getNotes(),
            'Removed tracking URLs should be mentioned in the notes.');
        self::assertStringContainsString('https://com2.example.com/', $creator->getNotes(),
            'Removed tracking URLs should be mentioned in the notes.');
    }
}
