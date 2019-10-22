<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanRepository;
use App\Utils\FilterItems;

class CountriesDataService
{
    /**
     * @var ArtisanRepository
     */
    private $artisanRepository;

    /**
     * @var array [ [ "name" => "...", "code" => "...", "region" => "..."], ... ]
     */
    private $data;

    public function __construct(ArtisanRepository $artisanRepository, string $projectDir)
    {
        $this->artisanRepository = $artisanRepository;

        $this->loadCountriesData($projectDir);
    }

    public function getFilterData(): FilterItems
    {
        $artisansCountries = $this->artisanRepository->getDistinctCountriesToCountAssoc();

        $result = $this->getRegionsFromCountries($this->data);
        $result->incUnknownCount($artisansCountries->getUnknownCount());

        foreach ($this->data as $countryData) {
            $code = $countryData['code'];
            $region = $countryData['region'];

            $countryCount = $artisansCountries->offsetExists($code) ? $artisansCountries[$code]->getCount() : 0;

            $result[$region]->incCount($countryCount);
            $result[$region]->getValue()->addComplexItem($code, $code, $countryData['name'], $countryCount);
        }

        return $result;
    }

    private function getRegionsFromCountries(array $countriesData): FilterItems
    {
        $regionNames = array_unique(array_map(function (array $country): string {
            return $country['region'];
        }, $countriesData));

        $result = new FilterItems(false);

        foreach ($regionNames as $regionName) {
            $result->addComplexItem($regionName, new FilterItems(false), $regionName, 0);
        }

        return $result;
    }

    private function loadCountriesData(string $projectDir): void
    {
        $this->data = json_decode(file_get_contents($projectDir.'/assets/countries.json'), true);
    }
}
