<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\Base\DStringMap;
use Veelkoov\Debris\StringSet;
use Veelkoov\Debris\StringStringMap;

class CountriesDataService
{
    /**
     * @var DStringMap<StringStringMap> 'Region name' => ['Country code' => 'Country name', ...]
     */
    private DStringMap $regions;

    /**
     * @param array<string, array<string, string>> $regions
     */
    public function __construct(
        #[Autowire(param: 'regions')] array $regions,
    ) {
        $this->regions = DStringMap::mapFrom($regions,
            static fn (array $countries, string $region) => [$region, new StringStringMap($countries)]);
    }

    public function getRegions(): StringSet
    {
        return $this->regions->getKeys();
    }

    public function getRegionFrom(string $countryCode): string
    {
        return $this->regions
            ->filterValues(static fn (StringStringMap $countries) => $countries->hasKey($countryCode))
            ->singleKey();
    }

    public function getNameFor(string $countryCode): string
    {
        return $this->regions
            ->filterValues(static fn (StringStringMap $countries) => $countries->hasKey($countryCode))
            ->singleValue()->get($countryCode);
    }
}
