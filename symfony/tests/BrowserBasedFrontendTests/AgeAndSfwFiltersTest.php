<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Data\Definitions\Ages;
use App\Tests\BrowserBasedFrontendTests\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\Cases\FuzzrakePantherTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Exception;
use Facebook\WebDriver\WebDriverBy;
use Psl\Iter;
use Symfony\Component\Panther\DomCrawler\Crawler;

/**
 * @large
 */
class AgeAndSfwFiltersTest extends FuzzrakePantherTestCase
{
    use MainPageTestsTrait;

    /**
     * @return array<array{0: ?Ages, 1: ?bool, 2: ?bool, 3: ?bool, 4: ?bool, 5: bool, 6: bool}>
     */
    private static function getCombinations(): array
    {
        $result = [];

        foreach ([null, ...Ages::cases()] as $ages) {
            foreach ([null, true, false] as $nsfwWebsite) {
                foreach ([null, true, false] as $nsfwSocial) {
                    foreach ([null, true, false] as $doesNsfw) {
                        foreach ([null, true, false] as $worksWithMinors) {
                            // TODO: Figure out a way to test DB inconsistencies as well #125
                            //       Listeners will be correcting some combinations here

                            $showToMinors = false === $nsfwWebsite
                                && false === $nsfwSocial
                                && (false === $doesNsfw || (null === $doesNsfw && Iter\contains([Ages::MIXED, Ages::MINORS], $ages)))
                                && true === $worksWithMinors;

                            $showAsSfw = false === $nsfwWebsite
                                && false === $nsfwSocial;

                            $result[] = [$ages, $nsfwWebsite, $nsfwSocial, $doesNsfw, $worksWithMinors, $showToMinors, $showAsSfw];
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @dataProvider ageAndSfwFiltersDataProvider
     *
     * @throws Exception
     */
    public function testAgeAndSfwFilters(bool $userIsMinor, ?bool $userWantsSfw): void
    {
        self::assertTrue(($userIsMinor && null === $userWantsSfw) || (!$userIsMinor && null !== $userWantsSfw));

        $creators = [];
        $expected = [];

        foreach (self::getCombinations() as $idx => $data) {
            [$ages, $nsfwWebsite, $nsfwSocial, $doesNsfw, $worksWithMinors, $showToMinors, $showAsSfw] = $data;

            $creatorId = sprintf('M%06d', $idx);

            $name = match ($ages) {
                Ages::MINORS => 'MIN',
                Ages::MIXED  => 'MIX',
                Ages::ADULTS => 'ADT',
                null         => 'UNK',
            };
            $name .= ' '.$this->descBool($nsfwWebsite, 'nWeb');
            $name .= ' '.$this->descBool($nsfwSocial, 'nSoc');
            $name .= ' '.$this->descBool($doesNsfw, 'nsfw');
            $name .= ' '.$this->descBool($worksWithMinors, 'wwMi');

            $creators[$creatorId] = (new Creator())
                ->setCreatorId($creatorId)
                ->setName($name)
                ->setAges($ages)
                ->setNsfwWebsite($nsfwWebsite)
                ->setNsfwSocial($nsfwSocial)
                ->setDoesNsfw($doesNsfw)
                ->setWorksWithMinors($worksWithMinors);

            if (($showToMinors && $userIsMinor) || ($showAsSfw && true === $userWantsSfw) || false === $userWantsSfw) {
                $expected[$creatorId] = $creators[$creatorId];
            }
        }
        self::persistAndFlush(...$creators);

        $this->clearCache();

        self::$client->request('GET', '/index.php/');

        $infoText = 'Currently '.count($creators).' makers from 0 countries are listed here.';
        self::$client->waitForElementToContain('.alert-dismissible p:not(.intro-updated-info)', $infoText, 5);

        self::$client->findElement(WebDriverBy::id('checklist-ill-be-careful'))->click();

        if ($userIsMinor) {
            self::waitUntilShows('#aasImNotAdult');
            self::$client->findElement(WebDriverBy::id('aasImNotAdult'))->click();
        } else {
            self::waitUntilShows('#aasImAdult');
            self::$client->findElement(WebDriverBy::id('aasImAdult'))->click();

            $lastChoiceId = true === $userWantsSfw ? 'aasKeepSfw' : 'aasAllowNsfw';
            self::waitUntilShows("#$lastChoiceId");
            self::$client->findElement(WebDriverBy::id($lastChoiceId))->click();
        }

        self::$client->findElement(WebDriverBy::id('checklist-dismiss-btn'))->click();

        $displayedCreatorIds = [];
        $crawler = self::$client->getCrawler();

        while (true) { // Handle multiple pages
            self::waitForLoadingIndicatorToDisappear();

            $displayedCreatorIds = [
                ...$displayedCreatorIds,
                ...$crawler->filter('#creators-table-body tr')
                    ->each(fn (Crawler $node, $_) => $node->attr('id', '')),
            ];

            if (0 < $crawler->filter('#next-items-page-link')->count()) {
                self::$client->findElement(WebDriverBy::id('next-items-page-link'))->click();
            } else {
                break;
            }
        }

        foreach ($expected as $creator) {
            self::assertContains($creator->getCreatorId(), $displayedCreatorIds, "Should display {$creator->getName()}");
        }

        foreach ($displayedCreatorIds as $creatorId) {
            self::assertIsString($creatorId); // Workaround lacking type hinting in crawler's each()
            self::assertArrayHasKey($creatorId, $expected, "Should not display {$creators[$creatorId]->getName()}");
        }
    }

    /**
     * @return array<array{0: bool, 1: ?bool}>
     */
    public function ageAndSfwFiltersDataProvider(): array
    {
        return [
            'Minor'    => [true,  null],
            'AdultSfw' => [false, true],
            'AdultAll' => [false, false],
        ];
    }

    private function descBool(?bool $value, string $description): string
    {
        return $description.'='.match ($value) {
            true  => 'Y',
            false => 'N',
            null  => '?',
        };
    }
}
