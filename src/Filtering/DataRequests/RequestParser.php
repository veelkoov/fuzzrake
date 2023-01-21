<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use Psl\Dict;
use Psl\Type;
use Symfony\Component\HttpFoundation\Request;

use function Psl\Iter\contains;

class RequestParser
{
    private const ARRAYS = [
        'countries',
        'states',
        'languages',
        'styles',
        'features',
        'orderTypes',
        'productionModels',
        'commissionStatuses',
        'species',
        'paymentPlans',
    ];

    private const BOOLEANS = [
        'isAdult',
        'wantsSfw',
    ];

    public function __construct(
        private readonly FiltersChoicesFilter $filter,
    ) {
    }

    public function getChoices(Request $request): Choices
    {
        $dataShape = Type\shape(Dict\from_keys(self::ARRAYS, fn ($_) => Type\vec(Type\string())));
        $strArrays = $dataShape->coerce(self::getStrArraysFromRequest($request));

        $dataShape = Type\shape(Dict\from_keys(self::BOOLEANS, fn ($_) => Type\bool()));
        $booleans = $dataShape->coerce(self::getBooleansFromRequest($request));

        $dataShape = Type\string();
        $makerId = $dataShape->coerce($request->get('makerId', ''));

        return $this->filter->getOnlyAllowed(new Choices(
            $makerId,
            $strArrays['countries'],
            $strArrays['states'],
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
        ));
    }

    private static function getStrArraysFromRequest(Request $request): mixed
    {
        return Dict\from_keys(self::ARRAYS, fn ($reqKey) => $request->get($reqKey, []));
    }

    private static function getBooleansFromRequest(Request $request): mixed
    {
        return Dict\from_keys(self::BOOLEANS, fn ($reqKey) => $request->get($reqKey, false));
    }
}
