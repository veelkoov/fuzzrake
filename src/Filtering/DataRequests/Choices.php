<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Service\CacheDigestProvider;
use Psl\Json;

class Choices implements CacheDigestProvider
{
    /**
     * @param list<string> $countries
     * @param list<string> $states
     * @param list<string> $languages
     * @param list<string> $styles
     * @param list<string> $features
     * @param list<string> $orderTypes
     * @param list<string> $productionModels
     * @param list<string> $commissionStatuses
     * @param list<string> $species
     */
    public function __construct(
        public readonly string $makerId,
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

    public function getCacheDigest(): string
    {
        return hash('sha256', Json\encode($this));
    }
}
