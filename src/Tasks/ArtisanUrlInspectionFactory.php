<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Repository\ArtisanUrlRepository;
use App\Service\WebpageSnapshotManager;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class ArtisanUrlInspectionFactory
{
    public function __construct(
        private ArtisanUrlRepository $artisanUrlRepository,
        private WebpageSnapshotManager $webpageSnapshotManager,
    ) {
    }

    public function get(SymfonyStyle $io): ArtisanUrlInspection
    {
        return new ArtisanUrlInspection($this->artisanUrlRepository, $this->webpageSnapshotManager, $io);
    }
}
