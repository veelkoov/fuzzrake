<?php

declare(strict_types=1);

namespace App\Tests\Management;

use App\Data\Definitions\ContactPermit;
use App\Management\UrlRemovalService;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Mx\GroupedUrl;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

#[Small]
class UrlRemovalServiceGetRemovalDataForTest extends TestCase
{
    public function testEmainNotSentWhenNoContactPermitted(): void
    {
        $creator = new Creator()->setContactAllowed(ContactPermit::NO)
            ->setWebsiteUrl('https://localhost/');

        $result = UrlRemovalService::getRemovalDataFor($creator, ['URL_WEBSITE_0']);

        self::assertFalse($result->sendEmail);
    }

    public function testEmailSentWhenContactPermitted(): void
    {
        $creator = new Creator()->setContactAllowed(ContactPermit::CORRECTIONS)
            ->setWebsiteUrl('https://localhost/');

        $result = UrlRemovalService::getRemovalDataFor($creator, ['URL_WEBSITE_0']);

        self::assertTrue($result->sendEmail);
    }

    public function testRemovalOfAllImportantLinksHides(): void
    {
        $creator = new Creator()
            ->setWebsiteUrl('https://localhost/')
            ->setCommissionsUrls(['https://localhost/'])
        ;

        $result = UrlRemovalService::getRemovalDataFor($creator, ['URL_WEBSITE_0']);

        self::assertTrue($result->hide);
    }

    public function testImportantLinksLeftDoNotHide(): void
    {
        $creator = new Creator()
            ->setWebsiteUrl('https://localhost/')
            ->setCommissionsUrls(['https://localhost/'])
        ;

        $result = UrlRemovalService::getRemovalDataFor($creator, ['URL_COMMISSIONS_0']);

        self::assertFalse($result->hide);
    }

    public function testNoRemovedUrlsThrowException(): void
    {
        self::expectException(InvalidArgumentException::class);
        UrlRemovalService::getRemovalDataFor(new Creator(), []);
    }

    public function testUnknownUrlIdThrowException(): void
    {
        $creator = new Creator()->setWebsiteUrl('https://localhost/');

        self::expectException(InvalidArgumentException::class);
        UrlRemovalService::getRemovalDataFor($creator, ['WRONG']);
    }

    public function testRemovedAndRemainingAreCalculatedProperly(): void
    {
        $creator = new Creator()
            ->setWebsiteUrl('https://localhost/main')
            ->setCommissionsUrls(['https://com1.example.com/', 'https://com2.example.com/'])
            ->setPricesUrls(['https://prc1.example.com/', 'https://prc2.example.com/'])
            ->setFaqUrl('https://localhost/faq')
        ;

        $result = UrlRemovalService::getRemovalDataFor($creator, ['URL_FAQ_0', 'URL_COMMISSIONS_1', 'URL_PRICES_0']);

        self::assertEqualsCanonicalizing(
            ['https://localhost/faq', 'https://com2.example.com/', 'https://prc1.example.com/'],
            Vec\map($result->removedUrls->urls, fn (GroupedUrl $url): string => $url->url),
        );

        self::assertEqualsCanonicalizing(
            ['https://localhost/main', 'https://com1.example.com/', 'https://prc2.example.com/'],
            Vec\map($result->remainingUrls->urls, fn (GroupedUrl $url): string => $url->url),
        );
    }
}
