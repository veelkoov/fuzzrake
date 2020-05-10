<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanRepository;

class FilterService
{
    private ArtisanRepository $artisanRepository;
    private CountriesDataService $countriesDataService;

    public function __construct(ArtisanRepository $artisanRepository, CountriesDataService $countriesDataService)
    {
        $this->artisanRepository = $artisanRepository;
        $this->countriesDataService = $countriesDataService;
    }

    public function getFiltersTplData()
    {
        return [
            'orderTypes'          => $this->artisanRepository->getDistinctOrderTypes(),
            'styles'              => $this->artisanRepository->getDistinctStyles(),
            'features'            => $this->artisanRepository->getDistinctFeatures(),
            'productionModels'    => $this->artisanRepository->getDistinctProductionModels(),
            'commissionsStatuses' => $this->artisanRepository->getDistinctCommissionStatuses(),
            'languages'           => $this->artisanRepository->getDistinctLanguages(),
            'countries'           => $this->countriesDataService->getFilterData(),
            'states'              => $this->artisanRepository->getDistinctStatesToCountAssoc(),
        ];
    }
}
