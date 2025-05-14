<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\TestUtils\UtcClockMock;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @medium
 */
class EventsControllerTest extends FuzzrakeWebTestCase
{
    public function testPageLoads(): void
    {
        self::$client->request('GET', '/events');

        self::assertResponseStatusCodeIs(200);
        static::assertSelectorTextContains('p', 'See all recently added makers');
    }

    /**
     * @dataProvider eventDescriptionDataProvider
     */
    public function testEventDescription(Event $event, string $expectedHtml): void
    {
        self::persistAndFlush($event);

        self::$client->request('GET', '/events');

        $actualHtml = self::$client->getCrawler()->filter('#events-list p')->html();

        self::assertEqualsIgnoringWhitespace($expectedHtml, $actualHtml);
    }

    /** @noinspection HtmlUnknownTarget */
    public function eventDescriptionDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            [
                (new Event())
                    ->setCreatorName('Creator name 1')
                    ->setCheckedUrls('https://getfursu.it/page1.html')
                    ->setType(Event::TYPE_CS_UPDATED)
                    ->setNowOpenFor("Commissions\nPre-mades")
                    ->setNoLongerOpenFor('Artistic liberty')
                    ->setTrackingIssues(true), '
                <strong>Creator name 1</strong> commissions status changed.
                No longer open for: Artistic liberty.
                <strong>Now open for: Commissions, Pre-mades.</strong>
                Encountered apparent difficulties during status analysis.
                Checked contents of:
                <a href="https://getfursu.it/page1.html" target="_blank">getfursu.it/page1.html</a>.',
            ],
            [
                (new Event())
                    ->setCreatorName('Creator name 2')
                    ->setCheckedUrls("https://getfursu.it/page2.html\nhttps://another.page/")
                    ->setType(Event::TYPE_CS_UPDATED)
                    ->setNowOpenFor('')
                    ->setNoLongerOpenFor('Pancakes')
                    ->setTrackingIssues(false), '
                <strong>Creator name 2</strong> commissions status changed.
                No longer open for: Pancakes.
                Checked contents of:
                <a href="https://getfursu.it/page2.html" target="_blank">getfursu.it/page2.html</a>,
                <a href="https://another.page/" target="_blank">another.page</a>.',
            ],
            [
                (new Event())
                    ->setCreatorName('One more creator')
                    ->setCheckedUrls('http://just-one-website/doc.php')
                    ->setType(Event::TYPE_CS_UPDATED)
                    ->setNowOpenFor("Carrots\nApples")
                    ->setNoLongerOpenFor('')
                    ->setTrackingIssues(true), '
                <strong>One more creator</strong> commissions status changed.
                <strong>Now open for: Carrots, Apples.</strong>
                Encountered apparent difficulties during status analysis.
                Checked contents of:
                <a href="http://just-one-website/doc.php" target="_blank">just-one-website/doc.php</a>.',
            ],
        );
    }

    public function testAtomFeedLoadsWithoutAnyEvents(): void
    {
        self::$client->request('GET', '/events-atom.xml');

        self::assertResponseStatusCodeIs(200);
    }

    /**
     * @throws DateTimeException
     */
    public function testAtomFeedShowsOnlyEventsYoungerThan4Days(): void
    {
        UtcClockMock::start();

        $fourDaysInSeconds = 4 * 24 * 60 * 60;
        $older = UtcClock::time() - $fourDaysInSeconds - 1;
        $younger = UtcClock::time() - $fourDaysInSeconds + 1;

        $eventVisible = (new Event())
            ->setType(Event::TYPE_GENERIC)
            ->setDescription('I should be visible in the Atom feed')
            ->setTimestamp(UtcClock::at("@$younger"));

        $eventHidden = (new Event())
            ->setType(Event::TYPE_GENERIC)
            ->setDescription('I should not appear in the Atom feed')
            ->setTimestamp(UtcClock::at("@$older"));

        self::persistAndFlush($eventVisible, $eventHidden);

        $contents = self::$client->request('GET', '/events-atom.xml')->outerHtml();

        self::assertStringContainsString('I should be visible in the Atom feed', $contents);
        self::assertStringNotContainsString('I should not appear in the Atom feed', $contents);
    }
}
