<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Utils\Json;
use App\Utils\Pagination\Pagination;
use JsonException;
use RuntimeException;
use Veelkoov\Debris\StringSet;

readonly class Choices
{
    public function __construct(
        public string $creatorId,
        public string $textSearch,
        public StringSet $countries,
        public StringSet $states,
        public StringSet $languages,
        public StringSet $styles,
        public StringSet $features,
        public StringSet $orderTypes,
        public StringSet $productionModels,
        public StringSet $openFor,
        public StringSet $species,
        public StringSet $paymentPlans,
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
            $this->creatorId,
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
            $this->paymentPlans,
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
        try {
            return hash('sha256', Json::encode($this));
        } catch (JsonException $exception) {
            throw new RuntimeException(previous: $exception);
        }
    }
}
