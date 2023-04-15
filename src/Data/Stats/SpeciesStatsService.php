<?php

declare(strict_types=1);

namespace App\Data\Stats;

use App\Data\Species\SpeciesService;
use App\Data\Stats\Compute\SpeciesCalculator;
use App\Repository\ArtisanRepository;
use App\Service\Cache;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\CacheTags;

class SpeciesStatsService
{
    public function __construct(
        private readonly ArtisanRepository $artisanRepository,
        private readonly SpeciesService $speciesService,
        private readonly Cache $cache,
    ) {
    }

    public function getStats(): SpeciesStats
    {
        return $this->cache->getCached('SpeciesStatsService.getStats', CacheTags::ARTISANS, function () {
            $artisans = Artisan::wrapAll($this->artisanRepository->getActive());

            return SpeciesCalculator::for($this->speciesService->getCompleteList())->add($artisans)->get();
        });
    }
}
