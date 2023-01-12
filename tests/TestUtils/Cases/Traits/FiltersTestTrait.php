<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\DataDefinitions\Ages;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

trait FiltersTestTrait
{
    /**
     * @return list<Artisan>
     */
    private function getCombinedFiltersTestSet(): array
    {
        return [
            $this->artisan('M000001', 'CZ', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Real life animals', 'Supported', false, false),
            $this->artisan('M000002', 'FI', 'State2', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Real life animals', 'Supported', false, false),
            $this->artisan('M000003', 'FI', 'State1', 'Lang2', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Real life animals', 'Supported', false, false),
            $this->artisan('M000004', 'FI', 'State1', 'Lang1', 'Realistic', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Real life animals', 'Supported', false, false),
            $this->artisan('M000005', 'FI', 'State1', 'Lang1', 'Toony', "LED eyes\nIndoor feet", 'Full plantigrade', 'Standard commissions', 'Open1', 'Real life animals', 'Supported', false, false),
            $this->artisan('M000006', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Tails (as parts/separate)', 'Standard commissions', 'Open1', 'Real life animals', 'Supported', false, false),
            $this->artisan('M000007', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Premades', 'Open1', 'Real life animals', 'Supported', false, false),
            $this->artisan('M000008', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open2', 'Real life animals', 'Supported', false, false),
            $this->artisan('M000009', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Fantasy creatures', 'Supported', false, false),
            $this->artisan('M000010', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Real life animals', 'None', false, false),
            $this->artisan('M000011', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Real life animals', 'Supported', true, false),
            $this->artisan('M000012', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Real life animals', 'Supported', false, true),
        ];
    }

    /**
     * @return list<array{list<Artisan>, array<string, list<string>|bool>, list<string>}>
     */
    public function filterChoicesDataProvider(): array
    {
        return [
            [
                self::getCombinedFiltersTestSet(),
                [],
                ['M000001', 'M000002', 'M000003', 'M000004', 'M000005', 'M000006', 'M000007', 'M000008', 'M000009', 'M000010', 'M000011', 'M000012'],
            ],

            [self::getCombinedFiltersTestSet(), ['countries'          => ['CZ']],                        ['M000001']],
            [self::getCombinedFiltersTestSet(), ['states'             => ['State2']],                    ['M000002']],
            [self::getCombinedFiltersTestSet(), ['languages'          => ['Lang2']],                     ['M000003']],
            [self::getCombinedFiltersTestSet(), ['styles'             => ['Realistic']],                 ['M000004']],
            [self::getCombinedFiltersTestSet(), ['features'           => ['LED eyes', 'Indoor feet']],   ['M000005']],
            [self::getCombinedFiltersTestSet(), ['orderTypes'         => ['Tails (as parts/separate)']], ['M000006']],
            [self::getCombinedFiltersTestSet(), ['productionModels'   => ['Premades']],                  ['M000007']],
            [self::getCombinedFiltersTestSet(), ['commissionStatuses' => ['Open2']],                     ['M000008']],
            [self::getCombinedFiltersTestSet(), ['species'            => ['Fantasy creatures']],         ['M000009']],
            [self::getCombinedFiltersTestSet(), ['paymentPlans'       => ['Not supported']],             ['M000010']],

            [
                self::getCombinedFiltersTestSet(),
                ['wantsSfw' => true],
                ['M000001', 'M000002', 'M000003', 'M000004', 'M000005', 'M000006', 'M000007', 'M000008', 'M000009', 'M000010', 'M000012'],
            ],

            [
                self::getCombinedFiltersTestSet(),
                ['isAdult' => false],
                ['M000012'],
            ],

            [
                self::getCombinedFiltersTestSet(),
                [
                    'countries'        => ['FI', 'CZ'],
                    'states'           => ['State1', 'State2'],
                    'styles'           => ['Toony', 'Realistic'],
                    'orderTypes'       => ['Tails (as parts/separate)', 'Full plantigrade'],
                    'productionModels' => ['Premades', 'Standard commissions'],

                    'languages'          => ['Lang1'],
                    'features'           => ['LED eyes'],
                    'commissionStatuses' => ['Open1'],
                    'species'            => ['Real life animals'],
                    'paymentPlans'       => ['Supported'],
                ],
                ['M000001', 'M000002', 'M000004', 'M000005', 'M000006', 'M000007', 'M000011', 'M000012'],
            ],
        ];
    }

    private function artisan(string $makerIdAndName, string $country, string $state, string $languages, string $styles, string $features, string $orderTypes, string $productionModels, string $openFor, string $speciesDoes, string $paymentPlans, bool $nsfw, bool $worksWithMinors): Artisan
    {
        return Artisan::new()
            ->setMakerId($makerIdAndName)
            ->setName($makerIdAndName)
            ->setCountry($country)
            ->setState($state)
            ->setLanguages($languages)
            ->setStyles($styles)
            ->setFeatures($features)
            ->setOrderTypes($orderTypes)
            ->setProductionModels($productionModels)
            ->setOpenFor($openFor)
            ->setSpeciesDoes($speciesDoes)
            ->setPaymentPlans($paymentPlans)
            ->setAges(Ages::ADULTS)
            ->setNsfwSocial($nsfw)
            ->setNsfwWebsite($nsfw)
            ->setDoesNsfw($nsfw)
            ->setWorksWithMinors($worksWithMinors)
        ;
    }
}
