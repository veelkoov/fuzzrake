<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Data\Definitions\Ages;
use App\Utils\Creator\SmartAccessDecorator as Creator;

trait FiltersTestTrait
{
    /**
     * @return list<Creator>
     */
    private static function getCombinedFiltersTestSet(): array
    {
        return [
            self::creator('M000001', 'CZ', 'State1', ['Lang1'], ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'], ['Open1'], ['Real life animals'], true, false, false),
            self::creator('M000002', 'FI', 'State2', ['Lang1'], ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'], ['Open1'], ['Real life animals'], true, false, false),
            self::creator('M000003', 'FI', 'State1', ['Lang2'], ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'], ['Open1'], ['Real life animals'], true, false, false),
            self::creator('M000004', 'FI', 'State1', ['Lang1'], ['Realistic'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'], ['Open1'], ['Real life animals'], true, false, false),
            self::creator('M000005', 'FI', 'State1', ['Lang1'], ['Toony'], ['LED eyes', 'Indoor feet'], ['Full plantigrade'], ['Standard commissions'], ['Open1'], ['Real life animals'], true, false, false),
            self::creator('M000006', 'FI', 'State1', ['Lang1'], ['Toony'], ['LED eyes'], ['Tails (as parts/separate)'], ['Standard commissions'], ['Open1'], ['Real life animals'], true, false, false),
            self::creator('M000007', 'FI', 'State1', ['Lang1'], ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Premades'], ['Open1'], ['Real life animals'], true, false, false),
            self::creator('M000008', 'FI', 'State1', ['Lang1'], ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'], ['Open2'], ['Real life animals'], true, false, false),
            self::creator('M000009', 'FI', 'State1', ['Lang1'], ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'], ['Open1'], ['Fantasy creatures'], true, false, false),
            self::creator('M000010', 'FI', 'State1', ['Lang1'], ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'], ['Open1'], [], false, false, false),
            self::creator('M000011', 'FI', 'State1', ['Lang1'], ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'], ['Open1'], ['Real life animals'], true, true, false),
            self::creator('M000012', 'FI', 'State1', ['Lang1'], ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'], ['Open1'], ['Real life animals'], true, false, true),
        ];
    }

    /**
     * @return list<Creator>
     */
    private static function getSpecialFiltersTestSet(): array
    {
        return [
            self::creator('NOCNTRY', '', 'State', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false),

            self::creator('NOSTATE', 'FI', '', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false),

            self::creator('NOLANGG', 'FI', 'State', [],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false),

            self::creator('NOSTLES', 'FI', 'State', ['Language'],
                [], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false),

            self::creator('BOTHSTL', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false,
                otherStyles: ['Other styles']),

            self::creator('OTHRSTL', 'FI', 'State', ['Language'],
                [], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false,
                otherStyles: ['Other styles']),

            self::creator('NOFTRES', 'FI', 'State', ['Language'],
                ['Toony'], [], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false),

            self::creator('BOTHFTR', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false,
                otherFeatures: ['Other features']),

            self::creator('OTHRFTR', 'FI', 'State', ['Language'],
                ['Toony'], [], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false,
                otherFeatures: ['Other features']),

            self::creator('NOORTPS', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], [], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false),

            self::creator('BOTHORT', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false,
                otherOrderTypes: ['Other order types']),

            self::creator('OTHRORT', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], [], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false,
                otherOrderTypes: ['Other order types']),

            self::creator('NOPRDMD', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], [],
                ['Open for'], ['Most species'], true, false, false),
        ];
    }

    /**
     * @return list<Creator>
     */
    private static function getPayPlanFiltersTestSet(): array
    {
        return [
            self::creator('UNKPAYP', 'FI', 'State', ['Language'], ['Toony'],
                ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], null, false, false),

            self::creator('NOPAYPL', 'FI', 'State', ['Language'], ['Toony'],
                ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], false, false, false),

            self::creator('PAYPLNS', 'FI', 'State', ['Language'], ['Toony'],
                ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Most species'], true, false, false),
        ];
    }

    /**
     * @return list<Creator>
     */
    private static function getTrackingFiltersTestSet(): array
    {
        return [
            self::creator('NTTRCKD', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                [], ['Most species'], true, false, false),

            self::creator('TRACKIS', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Offer'], ['Most species'], true, false, false)
                ->setCsTrackerIssue(true)->setCommissionsUrls(['url']),

            self::creator('TRKFAIL', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                [], ['Most species'], true, false, false)
                ->setCsTrackerIssue(true)->setCommissionsUrls(['url']),

            self::creator('TRACKOK', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Offer'], ['Most species'], true, false, false)
                ->setCommissionsUrls(['url']),
        ];
    }

    /**
     * @return list<Creator>
     */
    private static function getInactiveFiltersTestSet(): array
    {
        return [
            self::creator('ACTIVE1', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Canines'], true, false, false),

            self::creator('INACTIV', 'FI', 'State', ['Language'],
                ['Toony'], ['LED eyes'], ['Full plantigrade'], ['Standard commissions'],
                ['Open for'], ['Canines'], true, false, false,
                inactiveReason: 'Inactive'),
        ];
    }

    /**
     * @return array<string, array{list<Creator>, array<string, list<string>|bool>, list<string>}>
     */
    public static function filterChoicesDataProvider(): array
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

            't0' => [self::getTrackingFiltersTestSet(), [],                                 ['NTTRCKD', 'TRKFAIL', 'TRACKIS', 'TRACKOK']],
            't1' => [self::getTrackingFiltersTestSet(), ['openFor' => ['-']],               ['NTTRCKD']],
            't2' => [self::getTrackingFiltersTestSet(), ['openFor' => ['!']],               ['TRACKIS', 'TRKFAIL']],
            't3' => [self::getTrackingFiltersTestSet(), ['openFor' => ['!', '-']],          ['NTTRCKD', 'TRACKIS', 'TRKFAIL']],
            't4' => [self::getTrackingFiltersTestSet(), ['openFor' => ['Offer']],           ['TRACKIS', 'TRACKOK']],
            't5' => [self::getTrackingFiltersTestSet(), ['openFor' => ['-', 'Offer']],      ['NTTRCKD', 'TRACKIS', 'TRACKOK']],
            't6' => [self::getTrackingFiltersTestSet(), ['openFor' => ['!', 'Offer']],      ['TRKFAIL', 'TRACKIS', 'TRACKOK']],
            't7' => [self::getTrackingFiltersTestSet(), ['openFor' => ['-', '!', 'Offer']], ['NTTRCKD', 'TRKFAIL', 'TRACKIS', 'TRACKOK']],

            'u1' => [self::getCombinedFiltersTestSet(), ['species' => ['?']], ['M000010']],
        ];
    }

    /**
     * @param list<string> $languages
     * @param list<string> $styles
     * @param list<string> $features
     * @param list<string> $orderTypes
     * @param list<string> $productionModels
     * @param list<string> $openFor
     * @param list<string> $speciesDoes
     * @param list<string> $otherStyles
     * @param list<string> $otherFeatures
     * @param list<string> $otherOrderTypes
     * @param list<string> $speciesDoesnt
     */
    private static function creator(string $creatorIdAndName, string $country, string $state, array $languages, array $styles, array $features, array $orderTypes, array $productionModels, array $openFor, array $speciesDoes, ?bool $offersPaymentPlans, bool $nsfw, bool $worksWithMinors, array $otherStyles = [], array $otherFeatures = [], array $otherOrderTypes = [], array $speciesDoesnt = [], string $inactiveReason = ''): Creator
    {
        return new Creator()
            ->setCreatorId($creatorIdAndName)
            ->setName($creatorIdAndName)
            ->setCountry($country)
            ->setState($state)
            ->setLanguages($languages)
            ->setStyles($styles)
            ->setFeatures($features)
            ->setOrderTypes($orderTypes)
            ->setProductionModels($productionModels)
            ->setOpenFor($openFor)
            ->setSpeciesDoes($speciesDoes)
            ->setOffersPaymentPlans($offersPaymentPlans)
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
