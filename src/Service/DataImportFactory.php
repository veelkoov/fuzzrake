<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanRepository;
use App\Utils\Import\DataImport;
use App\Utils\Import\Manager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataImportFactory
{
    /**
     * @var ArtisanRepository
     */
    private $artisanRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(ArtisanRepository $artisanRepository, ObjectManager $objectManager)
    {
        $this->artisanRepository = $artisanRepository;
        $this->objectManager = $objectManager;
    }

    public function get(Manager $importManager, SymfonyStyle $io, bool $showFixCommands): DataImport
    {
        return new DataImport($this->artisanRepository, $this->objectManager, $importManager, $io, $showFixCommands);
    }
}
