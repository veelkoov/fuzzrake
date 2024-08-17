<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Service\CacheDigestProvider;
use Psl\Json;

readonly class Choices implements CacheDigestProvider
{
    /**
     * @param list<string> $countries
     * @param list<string> $states
     * @param list<string> $languages
     * @param list<string> $styles
     * @param list<string> $features
     * @param list<string> $orderTypes
     * @param list<string> $productionModels
     * @param list<string> $openFor
     * @param list<string> $species
     */
    public function __construct(
        public string $makerId,
        public string $textSearch,
        public array $countries,
        public array $states,
        public array $languages,
        public array $styles,
        public array $features,
        public array $orderTypes,
        public array $productionModels,
        public array $openFor,
        public array $species,
        public bool $wantsUnknownPaymentPlans,
        public bool $wantsAnyPaymentPlans,
        public bool $wantsNoPaymentPlans,
        public bool $isAdult,
        public bool $wantsSfw,
        public bool $wantsInactive,
        public bool $creatorMode,
    ) {
    }

    public function getCacheDigest(): string
    {
        return hash('sha256', Json\encode($this));
    }
}
