<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\ArtisanMetadata;
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

    public function import(array $artisansData): void
    {
        foreach ($artisansData as $artisanData) {
            $this->importSingle($artisanData);
        }
    }

    private function importSingle(array $artisanData): void
    {
        $artisan = new Artisan();
        $this->updateArtisanWithData($artisan, $artisanData);
        var_dump($artisan);
    }

    private function updateArtisanWithData(Artisan $artisan, array $newData): void
    {
        foreach (ArtisanMetadata::FIELDS as $fieldName => $modelFieldName) {
            if ($modelFieldName !== ArtisanMetadata::IGNORED_IU_FORM_FIELD) {
                $artisan->set($modelFieldName, $newData[ArtisanMetadata::uiFormIdx($fieldName)]);
            }
        }
    }
}