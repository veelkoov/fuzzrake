<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Service\WebpageSnapshotManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ArtisanUrlInspectionFactory
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WebpageSnapshotManager $webpageSnapshotManager,
    ) {
    }

    public function get(SymfonyStyle $io): ArtisanUrlInspection
    {
        return new ArtisanUrlInspection($this->entityManager, $this->webpageSnapshotManager, $io);
    }
}
