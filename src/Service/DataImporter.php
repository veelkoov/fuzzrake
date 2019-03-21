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
use App\Utils\Utils;
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

    public function import(array $artisansData, ImportCorrector $importCorrector, array $passcodes, SymfonyStyle $io,
                           bool $showFixCommands): void
    {
        $this->fixer = new DataFixer($io, false);
        $this->differ = new DataDiffer($io, $showFixCommands);
        $this->corrector = $importCorrector;

        $imports = $this->createImports($artisansData);
        $this->performImports($imports);

        $io->title('Showing artisans\' data before/after fixing');
        $this->showUpdatedArtisans($imports);
        $io->title('Validating updated artisans\' data and passcodes');
        $this->persistValidImports($imports, $passcodes, $io);
    }

    /**
     * @param array $artisansData
     *
     * @return ArtisanImport[]
     */
    private function createImports(array $artisansData): array
    {
        $result = [];

        foreach ($artisansData as $artisanData) {
            $import = $this->createImport($artisanData);
            $result[$import->getUpsertedArtisan()->getMakerId()] = $import; // Removes past duplicates
        }

        return $result;
    }

    private function createImport(array $artisanData): ArtisanImport
    {
        $result = new ArtisanImport($artisanData);
        $result->setNewData($this->updateArtisanWithData(new Artisan(), $artisanData));

        $result->setUpsertedArtisan($this->findBestMatchArtisan($artisanData) ?: new Artisan());
        $result->setOriginalArtisan(clone $result->getUpsertedArtisan()); // Clone unmodified

        return $result;
    }

    /**
     * @param ArtisanImport[] $imports
     */
    private function performImports(array $imports): void
    {
        foreach ($imports as $import) {
            $this->performImport($import);
        }
    }

    private function performImport(ArtisanImport $import): ArtisanImport
    {
        $this->updateArtisanWithData($import->getUpsertedArtisan(), $import->getRawNewData()); // Update the DB entity
        $this->fix($import->getUpsertedArtisan()); // And fix the DB entity

        return $import;
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
            $this->getDataColumn($artisanData, ArtisanMetadata::NAME),
            $this->getDataColumn($artisanData, ArtisanMetadata::FORMERLY),
            $this->corrector->getMatchedName($this->getDataColumn($artisanData, ArtisanMetadata::MAKER_ID))
        );

        if (count($results) > 1) {
            throw new DataImporterException('Expected no more than 1 artisan to be matched. Found: '.implode(', ', array_map(function (Artisan $artisan) { return "{$artisan->getName()} (ID {$artisan->getId()})"; }, $results)));
        }

        return array_pop($results);
    }

    /**
     * @param ArtisanImport[] $imports
     */
    private function showUpdatedArtisans(array $imports): void
    {
        foreach ($imports as $import) {
            $this->differ->showDiff($import->getOriginalArtisan(), $import->getUpsertedArtisan(), $import->getNewData());
        }
    }

    /**
     * @param ArtisanImport[] $imports
     * @param array           $passcodes
     */
    private function persistValidImports(array $imports, array $passcodes, SymfonyStyle $io)
    {
        foreach ($imports as $import) {
            $this->persistImportIfValid($passcodes, $io, $import);
        }
    }

    private function fix(Artisan $artisan): Artisan
    {
        $this->corrector->correctArtisan($artisan);
        $this->fixer->fixArtisanData($artisan);

        return $artisan;
    }

    private function getDataColumn(array $artisanData, string $prettyFieldName)
    {
        return $artisanData[ArtisanMetadata::getUiFormFieldIndexByPrettyName($prettyFieldName)];
    }

    private function persistImportIfValid(array $passcodes, SymfonyStyle $io, ArtisanImport $import): void
    {
        $new = $import->getUpsertedArtisan();
        $old = $import->getOriginalArtisan();
        $names = Utils::artisanNames($old, $new);
        $providedPasscode = $import->getProvidedPasscode();

        $this->fixer->validateArtisanData($new);
        $ok = true;

        if (null === $old->getId() && !$this->corrector->isAcknowledged($new->getMakerId())) {
            $io->warning("New maker: $names");
            $io->writeln([
                ImportCorrector::CMD_MATCH_NAME.":{$new->getMakerId()}:ABCDEFGHIJ:",
                ImportCorrector::CMD_ACK_NEW.":{$new->getMakerId()}:",
            ]);

            $ok = false;
        }

        if (!empty($old->getMakerId()) && $old->getMakerId() !== $new->getMakerId()) {
            $io->warning("$names changed their maker ID");
        }

        if (!array_key_exists($new->getMakerId(), $passcodes)) {
            $io->warning("$names set new passcode: $providedPasscode");
            $io->writeln("{$new->getMakerId()} $providedPasscode");

            $ok = false;
        } else {
            $expectedPasscode = $passcodes[$new->getMakerId()];

            if ($providedPasscode !== $expectedPasscode && !$this->corrector->ignoreInvalidPasscodeForData($import->getNewRawDataHash())) {
                $io->warning("$names provided invalid passcode '$providedPasscode' (expected: '$expectedPasscode')");
                $io->writeln(ImportCorrector::CMD_IGNORE_PIN.":{$new->getMakerId()}:{$import->getNewRawDataHash()}:");

                $ok = false;
            }
        }

        if ($ok) {
            $this->objectManager->persist($new);
        } elseif ($new->getId()) {
            $this->objectManager->refresh($new);
        }
    }
}
