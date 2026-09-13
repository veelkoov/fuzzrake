<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

readonly class FiltersData
{
    public function __construct(
        public FilterData $products,
        public FilterData $styles,
        public FilterData $paymentPlans,
        public FilterData $features,
        public FilterData $offers,
        public FilterData $openFor,
        public FilterData $languages,
        public FilterData $countries,
        public FilterData $states,
        public FilterData $species,
        public FilterData $inactive,
    ) {
    }
}
