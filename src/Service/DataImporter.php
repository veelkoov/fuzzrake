<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanRepository;
use Doctrine\Common\Persistence\ObjectManager;

class DataImporter
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

    public function import(array $arrayFromCsvFile): void
    {
        foreach ($arrayFromCsvFile as $arrayRow) {
            $this->importSingle($arrayRow);
        }
    }

    private function importSingle(array $arrayRow): void
    {
        var_dump($arrayRow);
    }
}