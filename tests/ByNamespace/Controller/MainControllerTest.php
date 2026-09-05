<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\UserCreator;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Creator\SmartOfferStatusAccessor;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[Medium]
class MainControllerTest extends FuzzrakeWebTestCase
{
    use ClockSensitiveTrait;

    public function testMainPageLoads(): void
    {
        self::persistAndFlush(UserCreator::get(true));

        self::$client->request('GET', '/');

        self::assertResponseStatusCodeIs(200);
        self::assertSelectorTextContains('#main-page-intro h4', 'Fursuit makers database');
    }

    /**
     * @throws DateTimeException
     */
    public function testRecentlyAddedPage(): void
    {
        self::mockTime();

        $creator1 = UserCreator::get()->setCreatorId('TEST001')->setName('Older creator 1')->setDateAdded(UtcClock::at('-43 days'));
        $creator2 = UserCreator::get()->setCreatorId('TEST002')->setName('Newer creator 2')->setDateAdded(UtcClock::at('-41 days'));
        $creator3 = UserCreator::get()->setCreatorId('TEST003')->setName('Newer creator 3')->setDateAdded(UtcClock::at('-40 days'))
            ->setFormerly(['Formerly 3A', 'Formerly 3B']);

        self::persistAndFlush($creator1, $creator2, $creator3);
        $this->clearCache();

        $crawler = self::$client->request('GET', '/new');
        self::assertResponseStatusCodeIs(200);

        self::assertEmpty($crawler->filterXPath('//li/a[text() = "Older creator 1"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer creator 2"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/a[text() = "Newer creator 3"]'));
        self::assertNotEmpty($crawler->filterXPath('//li/span[normalize-space(text()) = "/ Formerly 3A / Formerly 3B"]'));
    }

    #[DataProvider('commissionsStatusDisplayDataProvider')]
    public function testCommissionsStatusDisplay(Creator $creator, array $textsPresent, array $allTexts): void
    {
        self::persistAndFlush($creator);

        $crawler = self::$client->request('GET', '/c/'.$creator->getLastCreatorId());

        foreach ($textsPresent as $text) {
            self::assertNotFalse(strpos($crawler->html(), $text), "$text should appear on the page.");
        }

        foreach (array_diff($allTexts, $textsPresent) as $text) {
            self::assertFalse(stripos($crawler->html(), $text), "$text should not appear on the page.");
        }
    }

    /**
     * @return list<array{Creator, list<string>, list<string>}>
     */
    public static function commissionsStatusDisplayDataProvider(): array
    {
        $allTexts = [
            $notTracked = 'Not tracked.',
            $failed = 'Failed to detect.',
            $issues = 'Note: encountered apparent difficulties during detection; expect inaccuracies.',
            $tracked = 'Status is tracked and updated automatically based on the contents of',
            $learnMoreLabel = 'Learn more about automatic tracking',
            $learnMoreAttr = '/tracking"',
            $offer = 'PancakesOffer',
            $url = 'https://example.com/commissions',

            // Poor test design (traditionally), but we have the two exclusive alternatives,
            // so the test can't fail due to both not matching.
            $statusOpen = '<i class="text-success fa-solid fa-circle-check"></i> PancakesOffer',
            $statusClosed = '<i class="text-danger fa-solid fa-square-xmark"></i> PancakesOffer',
        ];

        return [
            'Not tracked' => [self::getCreator([], false, [], []),
                [$notTracked, $learnMoreLabel, $learnMoreAttr], $allTexts],
            'Tracked OK' => [self::getCreator([$url], false, ['PancakesOffer'], []),
                [$tracked, $url, $learnMoreLabel, $learnMoreAttr, $offer, $statusOpen], $allTexts],
            'Tracking issues' => [self::getCreator([$url], true, [], ['PancakesOffer']),
                [$tracked, $issues, $url, $learnMoreLabel, $learnMoreAttr, $offer, $statusClosed], $allTexts],
            'Tracking fail' => [self::getCreator([$url], true, [], []),
                [$failed, $url, $learnMoreLabel, $learnMoreAttr], $allTexts],
        ];
    }

    /**
     * @param list<string> $commissionsUrls
     * @param list<string> $openFor
     * @param list<string> $closedFor
     */
    private static function getCreator(array $commissionsUrls, bool $csTrackerIssue, array $openFor, array $closedFor): Creator
    {
        $creator = new Creator()->setCreatorId('TEST001')
            ->setCommissionsUrls($commissionsUrls);
        $creator->getVolatileData()->setCsTrackerIssue($csTrackerIssue);
        SmartOfferStatusAccessor::setList($creator, true, $openFor);
        SmartOfferStatusAccessor::setList($creator, false, $closedFor);

        return $creator;
    }
}
