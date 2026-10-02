<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Creator\SmartOfferStatusAccessor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class CreatorControllerTest extends FuzzrakeWebTestCase
{
    /**
     * @param list<string> $textsPresent
     * @param list<string> $allTexts
     */
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
     * @return array<string, array{Creator, list<string>, list<string>}>
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
