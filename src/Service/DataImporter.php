<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\ArtisanImport;
use App\Utils\ArtisanFields as Fields;
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

        $imports = $this->createImports($artisansData, $io);
        $this->performImports($imports);

        $io->title('Showing artisans\' data before/after fixing');
        $this->showUpdatedArtisans($imports);
        $io->title('Validating updated artisans\' data and passcodes');
        $this->persistValidImports($imports, $passcodes, $io);
    }

    /**
     * @param array $artisansData
     *
     * @param SymfonyStyle $io
     * @return ArtisanImport[]
     */
    private function createImports(array $artisansData, SymfonyStyle $io): array
    {
        $result = [];

        foreach ($artisansData as $artisanData) {
            $import = $this->createImport($artisanData);

            if ($this->corrector->isRejected($import->getNewRawDataHash())) {
                continue;
            }

            $makerId = $import->getUpsertedArtisan()->getMakerId() ?: $import->getNewData()->getMakerId();

            if (array_key_exists($makerId, $result)) {
                $io->note($import->getIdStringSafe().' was identified as an update to '.$result[$makerId]->getIdStringSafe());
            }

            $result[$makerId] = $import;
        }

        return $result;
    }

    private function createImport(array $artisanData): ArtisanImport
    {
        $result = new ArtisanImport($artisanData);
        $result->setNewData($this->updateArtisanWithData(new Artisan(), $artisanData));

        $result->setUpsertedArtisan($this->findBestMatchArtisan($result->getNewData()) ?: new Artisan());
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

    private function performImport(ArtisanImport $import): void
    {
        $this->updateArtisanWithData($import->getUpsertedArtisan(), $import->getRawNewData()); // Update the DB entity
        $this->fix($import->getUpsertedArtisan()); // And fix the DB entity
    }

    private function updateArtisanWithData(Artisan $artisan, array $newData): Artisan
    {
        foreach (Fields::persisted() as $field) {
            if ($field->isIncludedInUiForm()) {
                $newValue = $newData[$field->uiFormIndex()];

                if ($field->name() === Fields::MAKER_ID && $newValue !== $artisan->getMakerId()) {
                    $artisan->setFormerMakerIds(implode("\n", $artisan->getAllMakerIdsArr()));
                }

                $artisan->set($field->modelName(), $newValue);
            }
        }

        return $artisan;
    }

    private function findBestMatchArtisan(Artisan $artisan): ?Artisan
    {
        $artisan = $this->fix(clone $artisan); // Apply names & maker IDs fixes

        $results = $this->artisanRepository->findBestMatches(
            $artisan->getAllNamesArr(),
            $artisan->getAllMakerIdsArr(),
            $this->corrector->getMatchedName($artisan->getMakerId())
        );

        if (count($results) > 1) {
            throw new DataImporterException($this->getMoreThanOneArtisansMatchedMessage($artisan, $results));
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
        return $artisanData[ArtisanFields::getUiFormFieldIndexByPrettyName($prettyFieldName)];
    }

    private function persistImportIfValid(array $passcodes, SymfonyStyle $io, ArtisanImport $import): void
    {
        $new = $import->getUpsertedArtisan();
        $old = $import->getOriginalArtisan();
        $names = Utils::artisanNamesSafe($old, $new);
        $providedPasscode = $import->getProvidedPasscode();

        $this->fixer->validateArtisanData($new);
        $ok = true;

        if (null === $old->getId() && !$this->corrector->isAcknowledged($new->getMakerId())) {
            $io->warning("New maker: $names");
            $io->writeln([
                ImportCorrector::CMD_MATCH_NAME.":{$new->getMakerId()}:ABCDEFGHIJ:",
                ImportCorrector::CMD_ACK_NEW.":{$new->getMakerId()}:",
                ImportCorrector::CMD_REJECT.":{$new->getMakerId()}:{$import->getNewRawDataHash()}:",
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
                $io->writeln(ImportCorrector::CMD_REJECT.":{$new->getMakerId()}:{$import->getNewRawDataHash()}:");

                $ok = false;
            }
        }

        if ($ok) {
            $this->objectManager->persist($new);
        } elseif ($new->getId()) {
            $this->objectManager->refresh($new);
        }
    }

    private function getMoreThanOneArtisansMatchedMessage(Artisan $artisan, array $results): string
    {
        return 'Was looking for: ' . Utils::artisanNamesSafe($artisan) . '. Found more than one: '
            . implode(', ', array_map(function (Artisan $artisan) {
                return Utils::artisanNamesSafe($artisan);
            }, $results));
    }
}
