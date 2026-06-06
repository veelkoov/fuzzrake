<?php

declare(strict_types=1);

namespace App\Tests\BrowserBased;

use App\Data\Definitions\Ages;
use App\Tests\TestUtils\Cases\FuzzrakePantherTestCase;
use App\Tests\TestUtils\Cases\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\FiltersData;
use App\Tests\TestUtils\UserCreator;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Large;

#[Large]
class FiltersTest extends FuzzrakePantherTestCase
{
    use MainPageTestsTrait;

    /**
     * @return iterable<string, array{list<Creator>, array<string, list<string>|bool>, list<string>}>
     */
    public static function filtersInBrowserDataProvider(): iterable
    {
        $checklistCreators = [
            self::creator('TEST001', false, true),
            self::creator('TEST002', false, false),
            self::creator('TEST003', true, false),
        ];
        yield 'minor' => [$checklistCreators, ['isAdult' => false], ['TEST001']];
        yield 'adult_sfw' => [$checklistCreators, ['isAdult' => true, 'wantsSfw' => true], ['TEST001', 'TEST002']];
        yield 'adult_all' => [$checklistCreators, ['isAdult' => true, 'wantsSfw' => false], ['TEST001', 'TEST002', 'TEST003']];

        $stylesCreators = [
            self::creator('TEST001', styles: ['Toony']),
            self::creator('TEST002', styles: [], otherStyles: ['Unique style']),
            self::creator('TEST003', styles: [], otherStyles: []),
        ];
        yield 'styles_toony' => [$stylesCreators, ['styles' => ['Toony']], ['TEST001']];
        yield 'styles_unknown' => [$stylesCreators, ['styles' => ['Other']], ['TEST002']];
        yield 'styles_other' => [$stylesCreators, ['styles' => ['Unknown']], ['TEST003']];

        $openForCreators = [
            self::creator('TEST001', trackingIssues: true),
            self::creator('TEST002', notTracked: true),
        ];
        yield 'op_for_has_issues' => [$openForCreators, ['openFor' => ['Tracking issues']], ['TEST001']];
        yield 'op_for_not_tracked' => [$openForCreators, ['openFor' => ['Not tracked']], ['TEST002']];

        $speciesCreators = [
            self::creator('TEST001', speciesDoes: ['With antlers']),
            self::creator('TEST002', speciesDoes: ['Fantasy creatures']),
        ];
        yield 'some_species' => [$speciesCreators, ['species' => ['Fantasy creatures']], ['TEST002']];
    }

    /**
     * @param list<Creator>                    $creators
     * @param array<string, list<string>|bool> $filtersSet
     * @param list<string>                     $expectedCreatorIds
     *
     * @throws WebDriverException|JsonException
     *
     * Test filters by selecting the values by checkboxes' labels
     */
    #[DataProvider('filtersInBrowserDataProvider')]
    public function testFiltersInBrowser(array $creators, array $filtersSet, array $expectedCreatorIds): void
    {
        self::persistAndFlush(...$creators, ...FiltersData::entitiesFrom($creators));

        self::$client->request('GET', '/index.php/');

        $isAdult = (bool) ($filtersSet['isAdult'] ?? true);
        $wantsSfw = (bool) ($filtersSet['wantsSfw'] ?? false);

        $this->fillChecklist($isAdult, $wantsSfw);

        self::$client->findElement(WebDriverBy::id('open-filters-button'))->click();
        self::waitUntilShows('#filters-title');

        foreach ($filtersSet as $filter => $values) {
            if (is_bool($values)) {
                continue;
            }

            self::$client->findElement(WebDriverBy::cssSelector("#filter-ctrl-$filter > button"))->click();
            self::waitUntilShows("#filter-body-$filter");

            if ('species' === $filter) {
                $this->toggleSpecies('Most species');
            }

            foreach ($values as $value) {
                self::$client->findElement(WebDriverBy::xpath("//div[@id = \"filter-body-$filter\"]//label[contains(., \"$value\")]"))->click();
            }
        }

        self::$client->findElement(WebDriverBy::xpath('//button[normalize-space(text()) = "Apply"]'))->click();
        self::waitUntilHides('#filters-title', 1000);
        self::waitForLoadingIndicatorToDisappear();

        self::assertSelectorTextContains('#creators-table-pagination', 'Displaying '.count($expectedCreatorIds).' out of');

        foreach ($expectedCreatorIds as $creatorId) {
            self::assertSelectorIsVisible("tr#$creatorId");
        }
    }

    /**
     * @throws WebDriverException
     */
    private function toggleSpecies(string ...$specieNames): void
    {
        foreach ($specieNames as $specieName) {
            $xpath = '//input[@value="'.$specieName.'"]/ancestor::div[@role="group"]/span[contains(@class, "toggle")]';
            self::$client->findElement(WebDriverBy::xpath($xpath))->click();

            $xpath = '//input[@value="'.$specieName.'"]/ancestor::div[@role="group"]/following-sibling::fieldset';
            self::waitUntilShows($xpath);
        }
    }

    /**
     * @param list<string> $styles
     * @param list<string> $speciesDoes
     * @param list<string> $openFor
     * @param list<string> $otherStyles
     */
    private static function creator(string $creatorIdAndName, bool $nsfw = false, bool $worksWithMinors = true,
        array $styles = [], array $otherStyles = [], array $openFor = [], bool $trackingIssues = false,
        bool $notTracked = false, array $speciesDoes = [], string $inactiveReason = ''): Creator
    {
        return UserCreator::get()
            ->setCreatorId($creatorIdAndName)
            ->setName($creatorIdAndName)
            ->setStyles($styles)
            ->setOpenFor($openFor)
            ->setSpeciesDoes($speciesDoes)
            ->setAges(Ages::ADULTS)
            ->setNsfwSocial($nsfw)
            ->setNsfwWebsite($nsfw)
            ->setDoesNsfw($nsfw)
            ->setWorksWithMinors($worksWithMinors)
            ->setOtherStyles($otherStyles)
            ->setCsTrackerIssue($trackingIssues)
            ->setCommissionsUrls($notTracked ? [] : ['https://example.com/'])
            ->setInactiveReason($inactiveReason)
        ;
    }
}
