<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan as ArtisanE;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class Artisans
{
    private ?array $artisanEs = null;
    private ?array $artisans = null;

    public function __construct(
        private readonly ArtisanRepository $repository,
    ) {
    }

    /**
     * @return ArtisanE[]
     */
    public function getAllE(): array
    {
        return $this->artisanEs ??= $this->repository->getAll();
    }

    /**
     * @return Artisan[]
     */
    public function getAll(): array
    {
        return $this->artisans ??= Artisan::wrapAll($this->getAllE());
    }
}
