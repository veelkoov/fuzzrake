<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\DataDefinitions\Ages;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Exception;
use Facebook\WebDriver\WebDriverBy;

class AgeAndSfwFiltersTest extends PantherTestCaseWithEM
{
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
                            if (Ages::ADULTS !== $ages && true === $doesNsfw) {
                                continue; // TODO: Figure out a way to test such an DB inconsistency as well #125
                            }

                            if (true === $nsfwWebsite || true === $nsfwSocial || true === $doesNsfw) {
                                continue; // TODO: Figure out a way to test such an DB inconsistency as well #125
                            }

                            $showMinor = false === $nsfwWebsite
                                && false === $nsfwSocial
                                && false === $doesNsfw
                                && true === $worksWithMinors;

                            $showSfw = false === $nsfwWebsite
                                && false === $nsfwSocial;

                            $result[] = [$ages, $nsfwWebsite, $nsfwSocial, $doesNsfw, $worksWithMinors, $showMinor, $showSfw];
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

        foreach (self::getCombinations() as $data) {
            [$ages, $nsfwWebsite, $nsfwSocial, $doesNsfw, $worksWithMinors, $showMinor, $showSfw] = $data;

            $makerId = match ($ages) {
                Ages::MINORS => 'MIN',
                Ages::MIXED  => 'MIX',
                Ages::ADULTS => 'ADT',
                null         => 'UNK',
            };
            $makerId .= $this->letter($nsfwWebsite, 'W');
            $makerId .= $this->letter($nsfwSocial, 'S');
            $makerId .= $this->letter($doesNsfw, 'N');
            $makerId .= $this->letter($worksWithMinors, 'M');

            $artisans[$makerId] = (new Artisan())
                ->setMakerId($makerId)
                ->setName($makerId)
                ->setAges($ages)
                ->setNsfwWebsite($nsfwWebsite)
                ->setNsfwSocial($nsfwSocial)
                ->setDoesNsfw($doesNsfw)
                ->setWorksWithMinors($worksWithMinors);

            if (($showMinor && $userIsMinor) || ($showSfw && true === $userWantsSfw) || false === $userWantsSfw) {
                $expected[$makerId] = $artisans[$makerId];
            }
        }
        $this->persistAndFlush(...$artisans);

        $this->clearCache();

        $client->request('GET', '/');

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

        self::waitUntilShows('#data-table-container');

        foreach ($expected as $artisan) {
            self::assertVisible('#'.$artisan->getMakerId());
        }

        foreach ($artisans as $makerId => $artisan) {
            if (!array_key_exists($makerId, $expected)) {
                self::assertInvisible('#'.$artisan->getMakerId());
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

    private function letter(?bool $value, string $letter): string
    {
        if (null === $value) {
            return '0';
        }

        return $value ? $letter : '1';
    }
}
