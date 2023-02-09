<?php

declare(strict_types=1);

namespace App\Tests\Controller\Traits;

use App\DataDefinitions\Features;
use App\DataDefinitions\OrderTypes;
use App\DataDefinitions\ProductionModels;
use App\DataDefinitions\Styles;

trait FormsChoicesValuesAndLabelsTestTrait
{
    /**
     * @return array<string, list<list<array{value: string, label: string}>>>
     */
    public function formsChoicesValuesAndLabelsDataProvider(): array
    {
        $choices = [
            ...Features::getValues(),
            ...ProductionModels::getValues(),
            ...OrderTypes::getValues(),
            ...Styles::getValues(),
        ];

        $choices = array_map(fn (string $value) => ['value' => $value, 'label' => $value], $choices);

        // TODO https://github.com/veelkoov/fuzzrake/issues/184
        // foreach (Ages::cases() as $ages) {
        //     $choices[] = ['value' => $ages->value, 'label' => $ages->getLabel()];
        // }

        return [
            'All available choices' => [$choices],
        ];
    }
}
