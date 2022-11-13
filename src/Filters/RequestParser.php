<?php

declare(strict_types=1);

namespace App\Filters;

use App\Utils\Filters\SpecialItems;
use Psl\Dict;
use Psl\Type;
use Psl\Vec;
use Symfony\Component\HttpFoundation\Request;

final class RequestParser
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
        'paymentPlan'      => 'paymentPlans',
    ];

    public function getChoices(Request $request): Choices
    {
        $dataShape = Type\shape(Dict\from_keys(
            self::ARRAYS,
            fn ($_) => Type\vec(Type\string()),
        ));
        $data = $dataShape->coerce($this->getDataFromRequest($request));

        $unknownVal = SpecialItems::newUnknown()->getValue();
        $data['states'] = Vec\map($data['states'], fn ($value) => $value === $unknownVal ? '' : $value);

        return new Choices(...$data);
    }

    private function getDataFromRequest(Request $request): mixed
    {
        return Dict\map(Dict\flip(self::ARRAYS), fn ($reqKey) => $request->get($reqKey, []));
    }
}
