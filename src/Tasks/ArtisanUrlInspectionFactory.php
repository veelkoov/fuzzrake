<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Repository\ArtisanUrlRepository;
use App\Service\WebpageSnapshotManager;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ArtisanUrlInspectionFactory
{
    public function __construct(
        private readonly ArtisanUrlRepository $artisanUrlRepository,
        private readonly WebpageSnapshotManager $webpageSnapshotManager,
    ) {
    }

    public function get(SymfonyStyle $io): ArtisanUrlInspection
    {
        return new ArtisanUrlInspection($this->artisanUrlRepository, $this->webpageSnapshotManager, $io);
    }
}
