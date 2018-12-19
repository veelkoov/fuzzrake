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
        $artisan = $this->findBestMatchArtisan($artisanData) ?: new Artisan();

        $this->updateArtisanWithData($artisan, $artisanData);

        $this->objectManager->persist($artisan);
    }

    private function updateArtisanWithData(Artisan $artisan, array $newData): void
    {
        foreach (ArtisanMetadata::IU_FORM_TO_MODEL_FIELDS_MAP as $fieldName => $modelFieldName) {
            if ($modelFieldName !== ArtisanMetadata::IGNORED_IU_FORM_FIELD) {
                $artisan->set($modelFieldName, $newData[ArtisanMetadata::uiFormFieldIndexByName($fieldName)]);
            }
        }
    }

    private function findBestMatchArtisan(array $artisanData): ?Artisan
    {
        $results = $this->artisanRepository->findBestMatches(
            $artisanData[ArtisanMetadata::uiFormFieldIndexByName(ArtisanMetadata::NAME)],
            $artisanData[ArtisanMetadata::uiFormFieldIndexByName(ArtisanMetadata::FORMERLY)]
        );

        if (count($results) > 1) {
            throw new DataImporterException("Expected no more than 1 artisan to be matched. Found: " . implode(', ', array_map(function (Artisan $artisan) { return "{$artisan->getName()} (ID {$artisan->getId()})"; }, $results)));
        }

        return array_pop($results);
    }
}
