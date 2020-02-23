<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Service\WebpageSnapshotManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ArtisanUrlInspectionFactory
{
    private WebpageSnapshotManager $webpageSnapshotManager;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        WebpageSnapshotManager $webpageSnapshotManager
    ) {
        $this->entityManager = $entityManager;
        $this->webpageSnapshotManager = $webpageSnapshotManager;
    }

    public function get(SymfonyStyle $io): ArtisanUrlInspection
    {
        return new ArtisanUrlInspection($this->entityManager, $this->webpageSnapshotManager, $io);
    }
}
