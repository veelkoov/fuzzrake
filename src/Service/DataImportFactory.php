<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanRepository;
use App\Utils\Import\DataImport;
use App\Utils\Import\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataImportFactory
{
    private ArtisanRepository $artisanRepository;
    private EntityManagerInterface $objectManager;

    public function __construct(ArtisanRepository $artisanRepository, EntityManagerInterface $objectManager)
    {
        $this->artisanRepository = $artisanRepository;
        $this->objectManager = $objectManager;
    }

    public function get(Manager $importManager, SymfonyStyle $io, bool $showFixCommands): DataImport
    {
        return new DataImport($this->artisanRepository, $this->objectManager, $importManager, $io, $showFixCommands);
    }
}
