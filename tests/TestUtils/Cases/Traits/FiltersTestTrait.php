<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Data\Definitions\Ages;
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
            $this->artisan('M000010', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', '', 'None', false, false),
            $this->artisan('M000011', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Real life animals', 'Supported', true, false),
            $this->artisan('M000012', 'FI', 'State1', 'Lang1', 'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions', 'Open1', 'Real life animals', 'Supported', false, true),
        ];
    }

    /**
     * @return list<Artisan>
     */
    private function getSpecialFiltersTestSet(): array
    {
        return [
            $this->artisan('NOCNTRY', '', 'State', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false),

            $this->artisan('NOSTATE', 'FI', '', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false),

            $this->artisan('NOLANGG', 'FI', 'State', '',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false),

            $this->artisan('NOSTLES', 'FI', 'State', 'Language',
                '', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false),

            $this->artisan('BOTHSTL', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false,
                otherStyles: 'Other styles'),

            $this->artisan('OTHRSTL', 'FI', 'State', 'Language',
                '', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false,
                otherStyles: 'Other styles'),

            $this->artisan('NOFTRES', 'FI', 'State', 'Language',
                'Toony', '', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false),

            $this->artisan('BOTHFTR', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false,
                otherFeatures: 'Other features'),

            $this->artisan('OTHRFTR', 'FI', 'State', 'Language',
                'Toony', '', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false,
                otherFeatures: 'Other features'),

            $this->artisan('NOORTPS', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', '', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false),

            $this->artisan('BOTHORT', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false,
                otherOrderTypes: 'Other order types'),

            $this->artisan('OTHRORT', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', '', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false,
                otherOrderTypes: 'Other order types'),

            $this->artisan('NOPRDMD', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', '',
                'Open for', 'Most species', 'Supported', false, false),
        ];
    }

    /**
     * @return list<Artisan>
     */
    private function getPayPlanFiltersTestSet(): array
    {
        return [
            $this->artisan('UNKPAYP', 'FI', 'State', 'Language', 'Toony',
                'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', '', false, false),

            $this->artisan('NOPAYPL', 'FI', 'State', 'Language', 'Toony',
                'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'None', false, false),

            $this->artisan('PAYPLNS', 'FI', 'State', 'Language', 'Toony',
                'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Some plan', false, false),
        ];
    }

    /**
     * @return list<Artisan>
     */
    private function getTrackingFiltersTestSet(): array
    {
        return [
            $this->artisan('NTTRCKD', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                '', 'Most species', 'Supported', false, false),

            $this->artisan('TRACKIS', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false)
                ->setCsTrackerIssue(true)->setCommissionsUrls('url'),

            $this->artisan('TRKFAIL', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                '', 'Most species', 'Supported', false, false)
                ->setCsTrackerIssue(true)->setCommissionsUrls('url'),

            $this->artisan('TRACKOK', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Most species', 'Supported', false, false)
                ->setCommissionsUrls('url'),
        ];
    }

    /**
     * @return list<Artisan>
     */
    private function getInactiveFiltersTestSet(): array
    {
        return [
            $this->artisan('ACTIVE1', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Canines', 'Supported', false, false),

            $this->artisan('INACTIV', 'FI', 'State', 'Language',
                'Toony', 'LED eyes', 'Full plantigrade', 'Standard commissions',
                'Open for', 'Canines', 'Supported', false, false,
                inactiveReason: 'Inactive'),
        ];
    }

    /**
     * @return array<string, array{list<Artisan>, array<string, list<string>|bool>, list<string>}>
     */
    public function filterChoicesDataProvider(): array
    {
        return [
            'c1' => [
                self::getCombinedFiltersTestSet(),
                [],
                ['M000001', 'M000002', 'M000003', 'M000004', 'M000005', 'M000006', 'M000007', 'M000008', 'M000009', 'M000010', 'M000011', 'M000012'],
            ],

            'c2'  => [self::getCombinedFiltersTestSet(), ['countries'        => ['CZ']],                        ['M000001']],
            'c3'  => [self::getCombinedFiltersTestSet(), ['states'           => ['State2']],                    ['M000002']],
            'c4'  => [self::getCombinedFiltersTestSet(), ['languages'        => ['Lang2']],                     ['M000003']],
            'c5'  => [self::getCombinedFiltersTestSet(), ['styles'           => ['Realistic']],                 ['M000004']],
            'c6'  => [self::getCombinedFiltersTestSet(), ['features'         => ['LED eyes', 'Indoor feet']],   ['M000005']],
            'c7'  => [self::getCombinedFiltersTestSet(), ['orderTypes'       => ['Tails (as parts/separate)']], ['M000006']],
            'c8'  => [self::getCombinedFiltersTestSet(), ['productionModels' => ['Premades']],                  ['M000007']],
            'c9'  => [self::getCombinedFiltersTestSet(), ['openFor'          => ['Open2']],                     ['M000008']],
            'c10' => [self::getCombinedFiltersTestSet(), ['species'          => ['Fantasy creatures']],         ['M000009']],
            'c11' => [self::getCombinedFiltersTestSet(), ['paymentPlans'     => ['Not supported']],             ['M000010']],

            'c12' => [
                self::getCombinedFiltersTestSet(),
                ['wantsSfw' => true],
                ['M000001', 'M000002', 'M000003', 'M000004', 'M000005', 'M000006', 'M000007', 'M000008', 'M000009', 'M000010', 'M000012'],
            ],

            'c13' => [
                self::getCombinedFiltersTestSet(),
                ['isAdult' => false],
                ['M000012'],
            ],

            'c14' => [
                self::getCombinedFiltersTestSet(),
                [
                    'countries'        => ['FI', 'CZ'],
                    'states'           => ['State1', 'State2'],
                    'styles'           => ['Toony', 'Realistic'],
                    'orderTypes'       => ['Tails (as parts/separate)', 'Full plantigrade'],
                    'productionModels' => ['Premades', 'Standard commissions'],

                    'languages'    => ['Lang1'],
                    'features'     => ['LED eyes'],
                    'openFor'      => ['Open1'],
                    'species'      => ['Real life animals'],
                    'paymentPlans' => ['Supported'],
                ],
                ['M000001', 'M000002', 'M000004', 'M000005', 'M000006', 'M000007', 'M000011', 'M000012'],
            ],

            'i1' => [self::getInactiveFiltersTestSet(), ['inactive' => []], ['ACTIVE1']],
            'i2' => [self::getInactiveFiltersTestSet(), ['inactive' => ['.']], ['ACTIVE1', 'INACTIV']],

            's1'  => [self::getSpecialFiltersTestSet(), ['countries'        => ['?']], ['NOCNTRY']],
            's2'  => [self::getSpecialFiltersTestSet(), ['states'           => ['?']], ['NOSTATE']],
            's3'  => [self::getSpecialFiltersTestSet(), ['languages'        => ['?']], ['NOLANGG']],
            's4'  => [self::getSpecialFiltersTestSet(), ['styles'           => ['?']], ['NOSTLES']],
            's5'  => [self::getSpecialFiltersTestSet(), ['styles'           => ['*']], ['BOTHSTL', 'OTHRSTL']],
            's6'  => [self::getSpecialFiltersTestSet(), ['features'         => ['?']], ['NOFTRES']],
            's7'  => [self::getSpecialFiltersTestSet(), ['features'         => ['*']], ['BOTHFTR', 'OTHRFTR']],
            's8'  => [self::getSpecialFiltersTestSet(), ['orderTypes'       => ['?']], ['NOORTPS']],
            's9'  => [self::getSpecialFiltersTestSet(), ['orderTypes'       => ['*']], ['BOTHORT', 'OTHRORT']],
            's10' => [self::getSpecialFiltersTestSet(), ['productionModels' => ['?']], ['NOPRDMD']],

            'pp1' => [self::getPayPlanFiltersTestSet(), ['paymentPlans' => []],
                ['UNKPAYP', 'NOPAYPL', 'PAYPLNS']],
            'pp2' => [self::getPayPlanFiltersTestSet(), ['paymentPlans' => ['?']],
                ['UNKPAYP']],
            'pp3' => [self::getPayPlanFiltersTestSet(), ['paymentPlans' => ['?', 'Not supported']],
                ['UNKPAYP', 'NOPAYPL']],
            'pp4' => [self::getPayPlanFiltersTestSet(), ['paymentPlans' => ['Supported']],
                ['PAYPLNS']],
            'pp5' => [self::getPayPlanFiltersTestSet(), ['paymentPlans' => ['Supported', 'Not supported']],
                ['NOPAYPL', 'PAYPLNS']],
            'pp6' => [self::getPayPlanFiltersTestSet(), ['paymentPlans' => ['Supported', '?']],
                ['UNKPAYP', 'PAYPLNS']],
            'pp7' => [self::getPayPlanFiltersTestSet(), ['paymentPlans' => ['Supported', 'Not supported', '?']],
                ['UNKPAYP', 'NOPAYPL', 'PAYPLNS']],

            't1' => [self::getTrackingFiltersTestSet(), ['openFor' => ['-']],        ['NTTRCKD']],
            't2' => [self::getTrackingFiltersTestSet(), ['openFor' => ['!']],        ['TRACKIS', 'TRKFAIL']],
            't3' => [self::getTrackingFiltersTestSet(), ['openFor' => ['Open for']], ['TRACKIS', 'TRACKOK']],

            'u1' => [self::getCombinedFiltersTestSet(), ['species' => ['?']], ['M000010']],
        ];
    }

    private function artisan(string $makerIdAndName, string $country, string $state, string $languages, string $styles, string $features, string $orderTypes, string $productionModels, string $openFor, string $speciesDoes, string $paymentPlans, bool $nsfw, bool $worksWithMinors, string $otherStyles = '', string $otherFeatures = '', string $otherOrderTypes = '', string $speciesDoesnt = '', string $inactiveReason = ''): Artisan
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
            ->setOtherStyles($otherStyles)
            ->setOtherFeatures($otherFeatures)
            ->setOtherOrderTypes($otherOrderTypes)
            ->setSpeciesDoesnt($speciesDoesnt)
            ->setInactiveReason($inactiveReason)
        ;
    }
}
