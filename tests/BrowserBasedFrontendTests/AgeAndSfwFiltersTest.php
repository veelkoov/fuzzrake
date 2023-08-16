<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Data\Definitions\Ages;
use App\Tests\BrowserBasedFrontendTests\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Exception;
use Facebook\WebDriver\WebDriverBy;

use function Psl\Iter\contains;

/**
 * @large
 */
class AgeAndSfwFiltersTest extends PantherTestCaseWithEM
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
                                && (false === $doesNsfw || (null === $doesNsfw && contains([Ages::MIXED, Ages::MINORS], $ages)))
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

        $client = static::createPantherClient();

        $artisans = [];
        $expected = [];

        foreach (self::getCombinations() as $idx => $data) {
            [$ages, $nsfwWebsite, $nsfwSocial, $doesNsfw, $worksWithMinors, $showToMinors, $showAsSfw] = $data;

            $makerId = sprintf('M%06d', $idx);

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

            $artisans[$makerId] = (new Artisan())
                ->setMakerId($makerId)
                ->setName($name)
                ->setAges($ages)
                ->setNsfwWebsite($nsfwWebsite)
                ->setNsfwSocial($nsfwSocial)
                ->setDoesNsfw($doesNsfw)
                ->setWorksWithMinors($worksWithMinors);

            if (($showToMinors && $userIsMinor) || ($showAsSfw && true === $userWantsSfw) || false === $userWantsSfw) {
                $expected[$makerId] = $artisans[$makerId];
            }
        }
        $this->persistAndFlush(...$artisans);

        $this->clearCache();

        $client->request('GET', '/index.php/');

        $infoText = 'Currently '.count($artisans).' makers from 0 countries are listed here.';
        $client->waitForElementToContain('.alert-dismissible p:not(.intro-updated-info)', $infoText, 5);

        $client->findElement(WebDriverBy::id('checklist-ill-be-careful'))->click();

        if ($userIsMinor) {
            self::waitUntilShows('#aasImNotAdult');
            $client->findElement(WebDriverBy::id('aasImNotAdult'))->click();
        } else {
            self::waitUntilShows('#aasImAdult');
            $client->findElement(WebDriverBy::id('aasImAdult'))->click();

            $lastChoiceId = $userWantsSfw ? 'aasKeepSfw' : 'aasAllowNsfw';
            self::waitUntilShows("#$lastChoiceId");
            $client->findElement(WebDriverBy::id($lastChoiceId))->click();
        }

        $client->findElement(WebDriverBy::id('checklist-dismiss-btn'))->click();

        self::waitForLoadingIndicatorToDisappear();

        foreach ($expected as $artisan) {
            self::assertVisible('#'.$artisan->getMakerId(), "Should display {$artisan->getName()}");
        }

        foreach ($artisans as $makerId => $artisan) {
            if (!array_key_exists($makerId, $expected)) {
                self::assertInvisible('#'.$artisan->getMakerId(), "Should not display {$artisan->getName()}");
            }
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
