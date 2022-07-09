<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\Json;
use JsonException;

/**
 * @phpstan-type psCountryData array{name: string, code: string, region: string}
 */
class CountriesDataService
{
    /**
     * @var array<string, psCountryData>
     */
    private array $data;

    /**
     * @throws JsonException
     */
    public function __construct(
        string $projectDir,
    ) {
        $this->loadCountriesData($projectDir);
    }

    /**
     * @return string[]
     */
    public function getRegions(): array
    {
        $result = array_unique(array_map(fn (array $country): string => $country['region'], $this->data));
        sort($result);

        return $result;
    }

    public function getRegionFrom(string $countryCode): string
    {
        return $this->data[$countryCode]['region'];
    }

    public function getNameFor(string $countryCode): string
    {
        return $this->data[$countryCode]['name'];
    }

    /**
     * @throws JsonException
     */
    private function loadCountriesData(string $projectDir): void
    {
        /**
         * @var array<psCountryData> $dataNumberIndexes
         */
        $dataNumberIndexes = Json::readFile($projectDir.'/assets/countries.json');

        $dataCodeIndexes = [];

        foreach ($dataNumberIndexes as $country) {
            $dataCodeIndexes[$country['code']] = $country;
        }

        $this->data = $dataCodeIndexes;
    }
}
