<?php

declare(strict_types=1);

namespace App\Filtering;

use Psl\Json;

class Choices
{
    /**
     * @param string[] $countries
     * @param string[] $states
     * @param string[] $languages
     * @param string[] $styles
     * @param string[] $features
     * @param string[] $orderTypes
     * @param string[] $productionModels
     * @param string[] $commissionStatuses
     * @param string[] $species
     */
    public function __construct(
        public readonly array $countries,
        public readonly array $states,
        public readonly array $languages,
        public readonly array $styles,
        public readonly array $features,
        public readonly array $orderTypes,
        public readonly array $productionModels,
        public readonly array $commissionStatuses,
        public readonly array $species,
        public readonly bool $wantsUnknownPaymentPlans,
        public readonly bool $wantsAnyPaymentPlans,
        public readonly bool $wantsNoPaymentPlans,
        public readonly bool $isAdult,
        public readonly bool $wantsSfw,
    ) {
    }

    public function getDigest(): string
    {
        return hash('sha256', Json\encode($this));
    }
}
