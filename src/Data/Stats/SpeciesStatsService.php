<?php

declare(strict_types=1);

namespace App\Data\Stats;

use App\Data\Species\SpeciesService;
use App\Data\Stats\Compute\SpeciesCalculator;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class SpeciesStatsService
{
    public function __construct(
        private readonly ArtisanRepository $artisanRepository,
        private readonly SpeciesService $speciesService,
    ) {
    }

    public function getStats(): SpeciesStats
    {
        $artisans = Artisan::wrapAll($this->artisanRepository->getActive());

        return SpeciesCalculator::for($this->speciesService->getCompleteList())->add($artisans)->get();
    }
}
