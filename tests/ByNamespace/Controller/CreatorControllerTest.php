<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller;

use App\Data\LabelType;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Creator\SmartOfferStatusAccessor;
use Composer\Pcre\Preg;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\DomCrawler\Crawler;

#[Medium]
class CreatorControllerTest extends FuzzrakeWebTestCase
{
    private function hasElement(Crawler $crawler, string $element): bool
    {
        if (str_starts_with($element, '#')) {
            return Preg::isMatch($element, $crawler->html());
        } elseif (str_starts_with($element, '/')) {
            return $crawler->filterXPath($element)->count() > 0;
        } else {
            return str_contains($crawler->html(), $element);
        }
    }

    /**
     * @param list<string> $elementsPresent
     * @param list<string> $allElements
     */
    #[DataProvider('labelsDisplayDataProvider')]
    public function testLabelsDisplay(Creator $creator, array $elementsPresent, array $allElements): void
    {
        self::persistAndFlush($creator);

        $crawler = self::$client->request('GET', '/c/'.$creator->getLastCreatorId());

        foreach ($elementsPresent as $element) {
            self::assertNotFalse($this->hasElement($crawler, $element), "$element should appear on the page.");
        }

        foreach (array_diff($allElements, $elementsPresent) as $element) {
            self::assertFalse($this->hasElement($crawler, $element), "$element should not appear on the page.");
        }
    }

    /**
     * @return array<string, array{Creator, list<string>, list<string>}>
     */
    public static function labelsDisplayDataProvider(): array
    {
        $verifiedIcon = '<span class="verified-full"><i class="fa-solid fa-circle-check"></i></span>';
        $legacyVrfdIcon = '<span class="verified-legacy"><i class="fa-regular fa-circle-check"></i></span>';
        $unverifiedIcon = '<span class="not-verified"><i class="fa-regular fa-circle"></i></span>';

        $verifiedProduct = 'Head (as parts/separate)';
        $legacyVrfdProduct = 'Full plantigrade';
        $unverifiedProduct = 'Full digitigrade';

        $verifiedOffer = 'Standard commissions';
        $legacyVrfdOffer = 'Artistic liberty commissions';
        $unverifiedOffer = 'Premades';

        $allTexts = [
            $verifiedOfferListItem = "$verifiedIcon Standard</span> commissions",
            $legacyVrfdOfferListItem = "$legacyVrfdIcon Artistic</span> liberty commissions",
            $unverifiedOfferListItem = "$unverifiedIcon Premades",

            $verifiedProductListItem = "$verifiedIcon Head</span> (as parts/separate)",
            $legacyVrfdProductListItem = "$legacyVrfdIcon Full</span> plantigrade",
            $unverifiedProductListItem = "$unverifiedIcon Full</span> digitigrade",

            $verifiedIconExplanation   = "#$verifiedIcon\s+<span[^.]*>\s*Offers and products marked this way are verified\.#",
            $legacyVrfdIconExplanation = "#$legacyVrfdIcon\s+<span[^.]*>\s*Offers and products marked like this have been added before verification started in 2026\.#",
            $unverifiedIconExplanation = "#$unverifiedIcon\s+<span[^.]*>\s*Offers and products marked like this have not been verified yet\.#",

            $pendingReviewHeaderLabel  = '//div[contains(@class, "header")]//span[normalize-space(.)="Pending review"]/i',
            $before2026HeaderLabel     = '//div[contains(@class, "header")]//span[normalize-space(.)="Before 2026"]/i',
            $reviewed3timesHeaderLabel = '//div[contains(@class, "header")]//span[normalize-space(.)="Reviewed 3×"]/i',

            $pendingReviewBodyLabel  = '#<span[^>]+class="[^"]*creator-label[^"]*"[^>]*>\s*<i[^>]*></i>\s*Pending review\s*</span>\s*<span[^>]*>\s*This studio/maker has not yet been reviewed yet\.#',
            $before2026BodyLabel     = '#<span[^>]+class="[^"]*creator-label[^"]*"[^>]*>\s*<i[^>]*></i>\s*Before 2026\s*</span>\s*<span[^>]*>\s*This studio/maker has only been briefly reviewed, and added to the list before 2026\.#',
            $reviewed3timesBodyLabel = '#<span[^>]+class="[^"]*creator-label[^"]*"[^>]*>\s*<i[^>]*></i>\s*Reviewed 3×\s*</span>\s*<span[^>]*>\s*This studio/maker have been checked by 3\+ different reviewers during the 2026 reviews experiment\.#',
        ];

        return [
            'Creator 3 reviews'      => [self::getCreatorForLabelsDisplay(review3times: true), [$reviewed3timesHeaderLabel, $reviewed3timesBodyLabel], $allTexts],
            'Creator before 2026'    => [self::getCreatorForLabelsDisplay(verifiedBefore2026: true), [$before2026HeaderLabel, $before2026BodyLabel], $allTexts],
            'Creator pending review' => [self::getCreatorForLabelsDisplay(pendingReview: true), [$pendingReviewHeaderLabel, $pendingReviewBodyLabel], $allTexts],

            'Verified product'   => [self::getCreatorForLabelsDisplay(products: [$verifiedProduct => 'v']), [$verifiedProductListItem, $verifiedIconExplanation], $allTexts],
            'Leg.ver. product'   => [self::getCreatorForLabelsDisplay(products: [$legacyVrfdProduct => 'l']), [$legacyVrfdProductListItem, $legacyVrfdIconExplanation], $allTexts],
            'Unverified product' => [self::getCreatorForLabelsDisplay(products: [$unverifiedProduct => 'u']), [$unverifiedProductListItem, $unverifiedIconExplanation], $allTexts],

            'Verified offer'   => [self::getCreatorForLabelsDisplay(offers: [$verifiedOffer => 'v']), [$verifiedOfferListItem, $verifiedIconExplanation], $allTexts],
            'Leg.ver. offer'   => [self::getCreatorForLabelsDisplay(offers: [$legacyVrfdOffer => 'l']), [$legacyVrfdOfferListItem, $legacyVrfdIconExplanation], $allTexts],
            'Unverified offer' => [self::getCreatorForLabelsDisplay(offers: [$unverifiedOffer => 'u']), [$unverifiedOfferListItem, $unverifiedIconExplanation], $allTexts],

            'Mixed 1' => [self::getCreatorForLabelsDisplay(products: [$unverifiedProduct => 'u'], offers: [$unverifiedOffer => 'u']),
                [$unverifiedProductListItem, $unverifiedOfferListItem, $unverifiedIconExplanation], $allTexts],

            'Mixed 2' => [self::getCreatorForLabelsDisplay(products: [$unverifiedProduct => 'u'], offers: [$legacyVrfdOffer => 'l']),
                [$unverifiedProductListItem, $legacyVrfdOfferListItem, $unverifiedIconExplanation, $legacyVrfdIconExplanation], $allTexts],

            'Mixed 3' => [self::getCreatorForLabelsDisplay(verifiedBefore2026: true, products: [$unverifiedProduct => 'u'], offers: [$legacyVrfdOffer => 'l']),
                [$before2026BodyLabel, $before2026HeaderLabel, $unverifiedProductListItem, $legacyVrfdOfferListItem, $unverifiedIconExplanation, $legacyVrfdIconExplanation], $allTexts],

            'Mixed 4' => [self::getCreatorForLabelsDisplay(pendingReview: true, products: [$verifiedProduct => 'v'], offers: [$verifiedOffer => 'v']),
                [$pendingReviewBodyLabel, $pendingReviewHeaderLabel, $verifiedIconExplanation, $verifiedProductListItem, $verifiedOfferListItem], $allTexts],

            'Mixed 5' => [self::getCreatorForLabelsDisplay(pendingReview: true, products: [$unverifiedProduct => 'u', $legacyVrfdProduct => 'l'], offers: [$verifiedOffer => 'v']),
                [$pendingReviewBodyLabel, $pendingReviewHeaderLabel, $unverifiedIconExplanation, $verifiedIconExplanation, $legacyVrfdIconExplanation, $verifiedOfferListItem, $legacyVrfdProductListItem, $unverifiedProductListItem], $allTexts],
        ];
    }

    /**
     * @param array<string, 'u'|'l'|'v'> $products
     * @param array<string, 'u'|'l'|'v'> $offers
     */
    private static function getCreatorForLabelsDisplay(
        bool $verifiedBefore2026 = false,
        bool $review3times = false,
        bool $pendingReview = false,
        array $products = [],
        array $offers = [],
    ): Creator {
        $creator = new Creator()->setCreatorId('TEST001');

        if ($verifiedBefore2026) {
            $creator->setLabel(LabelType::CREATOR_ADDED_BEFORE_2026, '');
        }
        if ($review3times) {
            $creator->setLabel(LabelType::CREATOR_GOT_3_REVIEWS, '');
        }
        if ($pendingReview) {
            $creator->setLabel(LabelType::CREATOR_PENDING_REVIEW, '');
        }

        foreach ($products as $product => $ulv) {
            $creator->setProducts([...$creator->getProducts(), $product]);

            if ('u' !== $ulv) {
                $creator->setLabel('l' === $ulv ? LabelType::PRODUCT_VERIFIED_BEFORE_2026 : LabelType::PRODUCT_VERIFIED, $product);
            }
        }

        foreach ($offers as $offer => $ulv) {
            $creator->setOffers([...$creator->getOffers(), $offer]);

            if ('u' !== $ulv) {
                $creator->setLabel('l' === $ulv ? LabelType::OFFER_VERIFIED_BEFORE_2026 : LabelType::OFFER_VERIFIED, $offer);
            }
        }

        return $creator;
    }

    /**
     * @param list<string> $textsPresent
     * @param list<string> $allTexts
     */
    #[DataProvider('offerStatusDisplayDataProvider')]
    public function testOfferStatusDisplay(Creator $creator, array $textsPresent, array $allTexts): void
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
    public static function offerStatusDisplayDataProvider(): array
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
            'Not tracked' => [self::getCreatorForOfferStatusDisplay([], false, [], []),
                [$notTracked, $learnMoreLabel, $learnMoreAttr], $allTexts],
            'Tracked OK' => [self::getCreatorForOfferStatusDisplay([$url], false, ['PancakesOffer'], []),
                [$tracked, $url, $learnMoreLabel, $learnMoreAttr, $offer, $statusOpen], $allTexts],
            'Tracking issues' => [self::getCreatorForOfferStatusDisplay([$url], true, [], ['PancakesOffer']),
                [$tracked, $issues, $url, $learnMoreLabel, $learnMoreAttr, $offer, $statusClosed], $allTexts],
            'Tracking fail' => [self::getCreatorForOfferStatusDisplay([$url], true, [], []),
                [$failed, $url, $learnMoreLabel, $learnMoreAttr], $allTexts],
        ];
    }

    /**
     * @param list<string> $commissionsUrls
     * @param list<string> $openFor
     * @param list<string> $closedFor
     */
    private static function getCreatorForOfferStatusDisplay(array $commissionsUrls, bool $csTrackerIssue, array $openFor, array $closedFor): Creator
    {
        $creator = new Creator()->setCreatorId('TEST001')
            ->setCommissionsUrls($commissionsUrls);
        $creator->getVolatileData()->setCsTrackerIssue($csTrackerIssue);
        SmartOfferStatusAccessor::setList($creator, true, $openFor);
        SmartOfferStatusAccessor::setList($creator, false, $closedFor);

        return $creator;
    }
}
