<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\ArtisanImport;
use App\Utils\ArtisanMetadata;
use App\Utils\DataDiffer;
use App\Utils\DataFixer;
use App\Utils\ImportCorrector;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    /**
     * @var DataFixer
     */
    private $fixer;

    /**
     * @var DataDiffer
     */
    private $differ;

    /**
     * @var ImportCorrector
     */
    private $corrector;

    public function __construct(ArtisanRepository $artisanRepository, ObjectManager $objectManager)
    {
        $this->artisanRepository = $artisanRepository;
        $this->objectManager = $objectManager;
    }

    public function import(array $artisansData, ImportCorrector $importCorrector, SymfonyStyle $io): void
    {
        $this->fixer = new DataFixer($io);
        $this->differ = new DataDiffer($io);
        $this->corrector = $importCorrector;

        $imports = $this->performImports($artisansData);

        $io->title('Showing import data before/after fixing');
        $this->showFixedImportedData($imports);
        $io->title('Showing artisans\' data before/after fixing');
        $this->showUpdatedArtisans($imports);
        $io->title('Validating updated artisans\' data');
        $this->showValidationResults($imports);
    }

    private function performImports(array $artisansData)
    {
        return array_map(function (array $artisanData) {
            return $this->performImport($artisanData);
        }, $artisansData);
    }

    private function performImport(array $artisanData): ArtisanImport
    {
        $artisanImport = new ArtisanImport();

        $artisanImport->setUpsertedArtisan($this->findBestMatchArtisan($artisanData) ?: new Artisan());
        $artisanImport->setOriginalArtisan(clone $artisanImport->getUpsertedArtisan()); // Clone unmodified

        $this->updateArtisanWithData($artisanImport->getUpsertedArtisan(), $artisanData); // Now update the DB entity
        $this->fix($artisanImport->getUpsertedArtisan()); // And fix the DB entity

        $artisanImport->setNewOriginalData($this->updateArtisanWithData(new Artisan(), $artisanData));
        $artisanImport->setNewFixedData($this->fix(clone $artisanImport->getNewOriginalData()));

        $this->objectManager->persist($artisanImport->getUpsertedArtisan());

        return $artisanImport;
    }

    private function updateArtisanWithData(Artisan $artisan, array $newData): Artisan
    {
        foreach (ArtisanMetadata::PRETTY_TO_MODEL_FIELD_NAMES_MAP as $fieldName => $modelFieldName) {
            if (ArtisanMetadata::IGNORED_IU_FORM_FIELD !== $modelFieldName) {
                $artisan->set($modelFieldName, $newData[ArtisanMetadata::getUiFormFieldIndexByPrettyName($fieldName)]);
            }
        }

        return $artisan;
    }

    private function findBestMatchArtisan(array $artisanData): ?Artisan
    {
        $results = $this->artisanRepository->findBestMatches(
            $artisanData[ArtisanMetadata::getUiFormFieldIndexByPrettyName(ArtisanMetadata::NAME)],
            $artisanData[ArtisanMetadata::getUiFormFieldIndexByPrettyName(ArtisanMetadata::FORMERLY)]
        );

        if (count($results) > 1) {
            throw new DataImporterException('Expected no more than 1 artisan to be matched. Found: '.implode(', ', array_map(function (Artisan $artisan) { return "{$artisan->getName()} (ID {$artisan->getId()})"; }, $results)));
        }

        return array_pop($results);
    }

    /**
     * @param ArtisanImport[] $imports
     */
    private function showFixedImportedData(array $imports): void
    {
        foreach ($imports as $import) {
            $this->differ->showDiff($import->getNewOriginalData(), $import->getNewFixedData());
        }
    }

    /**
     * @param ArtisanImport[] $imports
     */
    private function showUpdatedArtisans(array $imports): void
    {
        foreach ($imports as $import) {
            $this->differ->showDiff($import->getOriginalArtisan(), $import->getNewFixedData());
        }
    }

    /**
     * @param ArtisanImport[] $imports
     */
    private function showValidationResults(array $imports)
    {
        foreach ($imports as $import) {
            $this->fixer->validateArtisanData($import->getUpsertedArtisan());
        }
    }

    private function fix(Artisan $artisan): Artisan
    {
        $this->fixer->fixArtisanData($artisan);
        $this->corrector->correctArtisan($artisan);

        return $artisan;
    }
}
