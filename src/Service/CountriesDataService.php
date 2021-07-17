<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanRepository;
use App\Utils\Filters\FilterData;
use App\Utils\Filters\Set;
use App\Utils\Json;
use JsonException;

class CountriesDataService
{
    /**
     * [ "code" => [ "name" => "...", "code" => "...", "region" => "..."], ... ].
     */
    private array $data;

    /**
     * @throws JsonException
     */
    public function __construct(
        private ArtisanRepository $artisanRepository,
        string $projectDir,
    ) {
        $this->loadCountriesData($projectDir);
    }

    public function getFilterData(): FilterData
    {
        $artisansCountries = $this->artisanRepository->getDistinctCountriesToCountAssoc();

        $result = $this->getRegionsFromCountries($this->data);
        $result->incUnknownCount($artisansCountries->getUnknownCount());

        foreach ($artisansCountries->getItems() as $country) {
            $code = $country->getValue();
            $region = $this->data[$code]['region'];

            $result->getItems()[$region]->incCount($country->getCount());
            $result->getItems()[$region]->getValue()->addComplexItem($code, $code, $this->data[$code]['name'],
                $country->getCount());
        }

        $this->sortInRegions($result);

        return $result;
    }

    private function getRegionsFromCountries(array $countriesData): FilterData
    {
        $regionNames = array_unique(array_map(fn (array $country): string => $country['region'], $countriesData));

        $result = new FilterData(false);

        foreach ($regionNames as $regionName) {
            $result->getItems()->addComplexItem($regionName, new Set(), $regionName, 0);
        }

        return $result;
    }

    /**
     * @throws JsonException
     */
    private function loadCountriesData(string $projectDir): void
    {
        $dataNumberIndexes = Json::decode(file_get_contents($projectDir.'/assets/countries.json'));
        $dataCodeIndexes = [];

        foreach ($dataNumberIndexes as $country) {
            $dataCodeIndexes[$country['code']] = $country;
        }

        $this->data = $dataCodeIndexes;
    }

    private function sortInRegions(FilterData $result): void
    {
        foreach ($result->getItems() as $item) {
            $item->getValue()->sort();
        }
    }
}
