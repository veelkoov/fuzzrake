<?php

declare(strict_types=1);

namespace App\Filtering;

use Psl\Dict;
use Psl\Type;
use Psl\Vec;
use Symfony\Component\HttpFoundation\Request;

use function Psl\Iter\contains;

class RequestParser
{
    private const ARRAYS = [
        'country'          => 'countries',
        'state'            => 'states',
        'language'         => 'languages',
        'style'            => 'styles',
        'feature'          => 'features',
        'orderType'        => 'orderTypes',
        'productionModel'  => 'productionModels',
        'commissionStatus' => 'commissionStatuses',
        'specie'           => 'species',
        'paymentPlan'      => 'paymentPlans',
    ];

    private const BOOLEANS = [
        'isAdult',
        'wantsSfw',
    ];

    public static function getChoices(Request $request): Choices
    {
        $dataShape = Type\shape(Dict\from_keys(self::ARRAYS, fn ($_) => Type\vec(Type\string())));
        $strArrays = $dataShape->coerce(self::getStrArraysFromRequest($request));

        $dataShape = Type\shape(Dict\from_keys(self::BOOLEANS, fn ($_) => Type\bool()));
        $booleans = $dataShape->coerce(self::getBooleansFromRequest($request));

        return new Choices( // TODO: Validate choices
            $strArrays['countries'],
            self::fixStates($strArrays['states']),
            $strArrays['languages'],
            $strArrays['styles'],
            $strArrays['features'],
            $strArrays['orderTypes'],
            $strArrays['productionModels'],
            $strArrays['commissionStatuses'],
            $strArrays['species'],
            contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_UNKNOWN),
            contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_PAYPLANS_SUPPORTED),
            contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_PAYPLANS_NONE),
            $booleans['isAdult'],
            $booleans['wantsSfw'],
        );
    }

    private static function getStrArraysFromRequest(Request $request): mixed
    {
        return Dict\map(Dict\flip(self::ARRAYS), fn ($reqKey) => $request->get($reqKey, []));
    }

    private static function getBooleansFromRequest(Request $request): mixed
    {
        return Dict\from_keys(self::BOOLEANS, fn ($reqKey) => $request->get($reqKey, false));
    }

    /**
     * Translates unknown values from filter context to data context: '?' ---> ''.
     *
     * @param string[] $states
     *
     * @return string[]
     */
    private static function fixStates(mixed $states): array
    {
        return Vec\map($states, fn ($value) => Consts::FILTER_VALUE_UNKNOWN === $value ? Consts::DATA_VALUE_UNKNOWN : $value);
    }
}
