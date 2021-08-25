<?php

declare(strict_types=1);

namespace App\Service;

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
        string $projectDir,
    ) {
        $this->loadCountriesData($projectDir);
    }

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
        $dataNumberIndexes = Json::decode(file_get_contents($projectDir.'/assets/countries.json'));
        $dataCodeIndexes = [];

        foreach ($dataNumberIndexes as $country) {
            $dataCodeIndexes[$country['code']] = $country;
        }

        $this->data = $dataCodeIndexes;
    }
}
