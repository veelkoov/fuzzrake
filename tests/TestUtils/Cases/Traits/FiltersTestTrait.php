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
    public function getTestArtisans(): array
    {
        return [
            $this->artisan('M000001', 'CZ', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Specie1', 'Supported', false, false),
            $this->artisan('M000002', 'FI', 'State2', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Specie1', 'Supported', false, false),
            $this->artisan('M000003', 'FI', 'State1', 'Lang2', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Specie1', 'Supported', false, false),
            $this->artisan('M000004', 'FI', 'State1', 'Lang1', 'Realistic', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Specie1', 'Supported', false, false),
            $this->artisan('M000005', 'FI', 'State1', 'Lang1', 'Toony', "LED eyes\nIndoor feet", 'Full plantigrade', 'Standard commissions', 'Open1', 'Specie1', 'Supported', false, false),
            $this->artisan('M000006', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Tails (as parts/separate)', 'Standard commissions', 'Open1', 'Specie1', 'Supported', false, false),
            $this->artisan('M000007', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Premades', 'Open1', 'Specie1', 'Supported', false, false),
            $this->artisan('M000008', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open2', 'Specie1', 'Supported', false, false),
            $this->artisan('M000009', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Specie2', 'Supported', false, false),
            $this->artisan('M000010', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Specie1', 'None', false, false),
            $this->artisan('M000011', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Specie1', 'Supported', true, false),
            $this->artisan('M000012', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Specie1', 'Supported', false, true),
        ];
    }

    /**
     * @return list<array{array<string, list<string>|bool>, list<string>}>
     */
    public function filterChoicesDataProvider(): array
    {
        return [
            [[
            ], [
                'M000001', 'M000002', 'M000003', 'M000004', 'M000005', 'M000006', 'M000007', 'M000008', 'M000009', 'M000010', 'M000011', 'M000012',
            ]],

            [['countries'          => ['CZ']],                        ['M000001']],
            [['states'             => ['State2']],                    ['M000002']],
            [['languages'          => ['Lang2']],                     ['M000003']],
            [['styles'             => ['Realistic']],                 ['M000004']],
            [['features'           => ['LED eyes', 'Indoor feet']],   ['M000005']],
            [['orderTypes'         => ['Tails (as parts/separate)']], ['M000006']],
            [['productionModels'   => ['Premades']],                  ['M000007']],
            [['commissionStatuses' => ['Open2']],                     ['M000008']],
            [['species'            => ['Specie2']],                   ['M000009']],
            [['paymentPlans'       => ['Not supported']],             ['M000010']],

            [[
                'wantsSfw' => true,
            ], [
                'M000001', 'M000002', 'M000003', 'M000004', 'M000005', 'M000006', 'M000007', 'M000008', 'M000009', 'M000010',
                'M000012',
            ]],

            [[
                'isAdult' => false,
            ], [
                'M000012',
            ]],

            [[
                'countries'        => ['FI', 'CZ'],
                'states'           => ['State1', 'State2'],
                'styles'           => ['Toony', 'Realistic'],
                'orderTypes'       => ['Tails (as parts/separate)', 'Full plantigrade'],
                'productionModels' => ['Premades', 'Standard commissions'],

                'languages'          => ['Lang1'],
                'features'           => ['LED eyes'],
                'commissionStatuses' => ['Open1'],
                'species'            => ['Specie1'],
                'paymentPlans'       => ['Supported'],
            ], [
                'M000001', 'M000002', 'M000004', 'M000005', 'M000006', 'M000007',
                'M000011', 'M000012',
            ]],
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
