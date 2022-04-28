<?php

declare(strict_types=1);

namespace App\Utils\Filters;

class FiltersData
{
    public function __construct(
        public readonly FilterData $orderTypes,
        public readonly FilterData $styles,
        public readonly FilterData $paymentPlans,
        public readonly FilterData $features,
        public readonly FilterData $productionModels,
        public readonly FilterData $commissionsStatuses,
        public readonly FilterData $languages,
        public readonly FilterData $countries,
        public readonly FilterData $states,
        public readonly Set $species,
        public readonly FilterData $worksWithMinors,
    ) {
    }
}
