<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Utils\Collections\StringList;
use App\Utils\Pagination\Pagination;
use Psl\Json;

readonly class Choices
{
    public function __construct(
        public string $makerId,
        public string $textSearch,
        public StringList $countries,
        public StringList $states,
        public StringList $languages,
        public StringList $styles,
        public StringList $features,
        public StringList $orderTypes,
        public StringList $productionModels,
        public StringList $openFor,
        public StringList $species,
        public bool $wantsUnknownPaymentPlans,
        public bool $wantsAnyPaymentPlans,
        public bool $wantsNoPaymentPlans,
        public bool $isAdult,
        public bool $wantsSfw,
        public bool $wantsInactive,
        public bool $creatorMode,
        public int $pageNumber,
        public int $pageSize = Pagination::PAGE_SIZE,
    ) {
    }

    public function changePage(int $newPageNumber): self
    {
        return new self(
            $this->makerId,
            $this->textSearch,
            $this->countries,
            $this->states,
            $this->languages,
            $this->styles,
            $this->features,
            $this->orderTypes,
            $this->productionModels,
            $this->openFor,
            $this->species,
            $this->wantsUnknownPaymentPlans,
            $this->wantsAnyPaymentPlans,
            $this->wantsNoPaymentPlans,
            $this->isAdult,
            $this->wantsSfw,
            $this->wantsInactive,
            $this->creatorMode,
            $newPageNumber,
            $this->pageSize,
        );
    }

    public function getCacheDigest(): string
    {
        return hash('sha256', Json\encode($this));
    }
}
