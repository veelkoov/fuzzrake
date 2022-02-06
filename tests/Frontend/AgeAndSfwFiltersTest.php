<?php

declare(strict_types=1);

namespace App\Tests\Frontend;

use App\DataDefinitions\Ages;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Exception;
use Facebook\WebDriver\WebDriverBy;

class AgeAndSfwFiltersTest extends PantherTestCaseWithEM
{
    private static function getCombinations(): array
    {
        $result = [];

        foreach ([null, ...Ages::cases()] as $ages) {
            foreach ([null, true, false] as $nsfwWebsite) {
                foreach ([null, true, false] as $nsfwSocial) {
                    foreach ([null, true, false] as $doesNsfw) {
                        foreach ([null, true, false] as $worksWithMinors) {
                            if (Ages::ADULTS !== $ages && true === $doesNsfw) {
                                continue; // TODO: Figure out a way to test such an DB inconsistency as well
                            }

                            if (true === $nsfwWebsite || true === $nsfwSocial || true === $doesNsfw) {
                                continue; // TODO: Figure out a way to test such an DB inconsistency as well
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

        $client->request('GET', '/');
        $client->waitForElementToContain('.alert-dismissible p', 'Currently '.count($artisans).' makers from 0 countries are listed here.', 1);

        $client->findElement(WebDriverBy::id('checklist-ill-be-careful'))->click();

        self::waitUntilShows('#checklist-ack-pros-and-cons');
        $client->findElement(WebDriverBy::id('checklist-ack-pros-and-cons'))->click();

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
