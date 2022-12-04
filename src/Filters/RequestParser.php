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
        'specie'           => 'species',
        'paymentPlan'      => 'paymentPlans',
    ];

    private const BOOLEANS = [
        'isAdult',
        'wantsSfw',
    ];

    public function getChoices(Request $request): Choices
    {
        $dataShape = Type\shape(
            Dict\merge(
                Dict\from_keys(
                    self::ARRAYS,
                    fn ($_) => Type\vec(Type\string()),
                ),
                Dict\from_keys(
                    self::BOOLEANS,
                    fn ($_) => Type\bool(),
                ),
            ),
        );

        $data = $dataShape->coerce($this->getDataFromRequest($request));
        $data['states'] = $this->fixStates($data['states']); // @phpstan-ignore-line

        return new Choices(...$data); // @phpstan-ignore-line
    }

    private function getDataFromRequest(Request $request): mixed
    {
        $result = Dict\merge(
            Dict\map(Dict\flip(self::ARRAYS), fn ($reqKey) => $request->get($reqKey, [])),
            Dict\from_keys(
                self::BOOLEANS,
                fn ($reqKey) => $request->get($reqKey, false),
            ),
        );

        return $result;
    }

    /**
     * @param string[] $states
     *
     * @return string[]
     */
    private function fixStates(mixed $states): array
    {
        $unknownVal = SpecialItems::newUnknown()->getValue();

        return Vec\map($states, fn ($value) => $value === $unknownVal ? '' : $value);
    }
}
