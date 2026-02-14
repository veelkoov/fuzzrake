<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\Base\DStringMap;
use Veelkoov\Debris\Maps\StringToString;
use Veelkoov\Debris\Sets\StringSet;

class CountriesDataService
{
    /**
     * @var DStringMap<StringToString> 'Region name' => ['Country code' => 'Country name', ...]
     */
    private readonly DStringMap $regions;

    /**
     * @param array<string, array<string, string>> $regions
     */
    public function __construct(
        #[Autowire(param: 'regions')] array $regions,
    ) {
        $this->regions = DStringMap::mapFrom($regions,
            static fn (array $countries, string $region) => [$region, new StringToString($countries)]);
    }

    public function getRegions(): StringSet
    {
        return $this->regions->getKeys();
    }

    public function getRegionFrom(string $countryCode): string
    {
        return $this->regions
            ->filterValues(static fn (StringToString $countries) => $countries->hasKey($countryCode))
            ->singleKey();
    }

    public function getNameFor(string $countryCode): string
    {
        return $this->regions
            ->filterValues(static fn (StringToString $countries) => $countries->hasKey($countryCode))
            ->singleValue()->get($countryCode);
    }
}
