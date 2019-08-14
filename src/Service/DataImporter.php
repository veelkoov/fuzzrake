<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\ArtisanFields as Fields;
use App\Utils\DataDiffer;
use App\Utils\DataFixer;
use App\Utils\DateTimeException;
use App\Utils\DateTimeUtils;
use App\Utils\Import\Corrector;
use App\Utils\Import\ImportException;
use App\Utils\Import\Row;
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
     * @var Corrector
     */
    private $corrector;

    public function __construct(ArtisanRepository $artisanRepository, ObjectManager $objectManager)
    {
        $this->artisanRepository = $artisanRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * @param array        $artisansData
     * @param Corrector    $importCorrector
     * @param SymfonyStyle $io
     * @param bool         $showFixCommands
     *
     * @throws ImportException
     */
    public function import(array $artisansData, Corrector $importCorrector, SymfonyStyle $io, bool $showFixCommands): void
    {
        $this->fixer = new DataFixer($io, false);
        $this->differ = new DataDiffer($io, $showFixCommands);
        $this->corrector = $importCorrector;

        $imports = $this->createImports($artisansData, $io);
        $this->performImports($imports);

        $io->title('Showing artisans\' data before/after fixing');
        $this->showUpdatedArtisans($imports);
        $io->title('Validating updated artisans\' data and passcodes');
        $this->persistValidImports($imports, $io);
    }

    /**
     * @param array        $artisansData
     * @param SymfonyStyle $io
     *
     * @return Row[]
     *
     * @throws ImportException
     */
    private function createImports(array $artisansData, SymfonyStyle $io): array
    {
        $result = [];

        foreach ($artisansData as $artisanData) {
            $row = $this->createImportRow($artisanData);

            if ($this->corrector->isRejected($row)) {
                continue;
            }

            if ($this->corrector->isDelayed($row)) {
                $io->note("Ignoring {$row->getIdStringSafe()} until {$this->corrector->getIgnoredUntilDate($row)->format('Y-m-d')}");
                continue;
            }

            $makerId = $row->getArtisan()->getMakerId() ?: $row->getInput()->getMakerId();

            if (array_key_exists($makerId, $result)) {
                $io->note($row->getIdStringSafe().' was identified as an update to '.$result[$makerId]->getIdStringSafe());
            }

            $result[$makerId] = $row;
        }

        return $result;
    }

    /**
     * @param array $artisanData
     *
     * @return Row
     *
     * @throws ImportException
     */
    private function createImportRow(array $artisanData): Row
    {
        try {
            $result = new Row($artisanData);
        } catch (DateTimeException $e) {
            throw new ImportException("Failed parsing import row's date", 0, $e);
        }

        $result->setInput($this->updateArtisanWithData(new Artisan(), $artisanData));

        $result->setArtisan($this->findBestMatchArtisan($result->getInput()) ?: new Artisan());
        $result->setOriginalArtisan(clone $result->getArtisan()); // Clone unmodified

        return $result;
    }

    private function performImports(array $imports): void
    {
        foreach ($imports as $import) {
            $this->performImport($import);
        }
    }

    private function performImport(Row $import): void
    {
        $this->updateArtisanWithData($import->getArtisan(), $import->getRawInput()); // Update the DB entity
        $this->fix($import->getArtisan()); // And fix the DB entity
    }

    private function updateArtisanWithData(Artisan $artisan, array $newData): Artisan
    {
        foreach (Fields::persisted() as $field) {
            if ($field->isIncludedInUiForm()) {
                $newValue = $newData[$field->uiFormIndex()];

                if (Fields::MAKER_ID === $field->name() && $newValue !== $artisan->getMakerId()) {
                    $artisan->setFormerMakerIds(implode("\n", $artisan->getAllMakerIdsArr()));
                }

                $artisan->set($field->modelName(), $newValue);
            }
        }

        return $artisan;
    }

    /**
     * @param Artisan $artisan
     *
     * @return Artisan|null
     *
     * @throws ImportException
     */
    private function findBestMatchArtisan(Artisan $artisan): ?Artisan
    {
        $artisan = $this->fix(clone $artisan); // Apply names & maker IDs fixes

        $results = $this->artisanRepository->findBestMatches(
            $artisan->getAllNamesArr(),
            $artisan->getAllMakerIdsArr(),
            $this->corrector->getMatchedName($artisan->getMakerId())
        );

        if (count($results) > 1) {
            throw new ImportException($this->getMoreThanOneArtisansMatchedMessage($artisan, $results));
        }

        return array_pop($results);
    }

    private function showUpdatedArtisans(array $imports): void
    {
        foreach ($imports as $import) {
            $this->differ->showDiff($import->getOriginalArtisan(), $import->getArtisan(), $import->getInput());
        }
    }

    private function fix(Artisan $artisan): Artisan
    {
        $this->corrector->correctArtisan($artisan);
        $this->fixer->fixArtisanData($artisan);

        return $artisan;
    }

    private function persistValidImports(array $imports, SymfonyStyle $io): void
    {
        foreach ($imports as $import) {
            $this->persistImportIfValid($io, $import);
        }
    }

    private function persistImportIfValid(SymfonyStyle $io, Row $row): void
    {
        $new = $row->getArtisan();
        $old = $row->getOriginalArtisan();
        $this->fixer->validateArtisanData($new);
        $ok = true;

        if (null === $old->getId() && !$this->corrector->isAcknowledged($row->getMakerId())) {
            $this->reportNewMaker($io, $row);
            $ok = false;
        }

        if (!empty($old->getMakerId()) && $old->getMakerId() !== $row->getMakerId()) {
            $this->reportChangedMakerId($io, $row);
        }

        if ('' === ($expectedPasscode = $row->getArtisan()->getPrivateData()->getPasscode())) {
            $this->reportNewPasscode($io, $row);

            $ok = false;
        } elseif ($row->getProvidedPasscode() !== $expectedPasscode && !$this->corrector->shouldIgnorePasscode($row)) {
            $this->reportInvalidPasscode($io, $row, $expectedPasscode);

            $ok = false;
        }

        if ($ok) {
            $this->objectManager->persist($new);
        } elseif ($new->getId()) {
            $this->objectManager->refresh($new);
        }
    }

    private function getMoreThanOneArtisansMatchedMessage(Artisan $artisan, array $results): string
    {
        return 'Was looking for: '.Utils::artisanNamesSafeForCli($artisan).'. Found more than one: '
            .implode(', ', array_map(function (Artisan $artisan) {
                return Utils::artisanNamesSafeForCli($artisan);
            }, $results));
    }

    private function reportNewMaker(SymfonyStyle $io, Row $row): void
    {
        $monthLater = DateTimeUtils::getMonthLaterYmd();
        $makerId = $row->getArtisan()->getMakerId();

        $io->warning("New maker: {$row->getNames()}");
        $io->writeln([
            Corrector::CMD_MATCH_NAME.":$makerId:ABCDEFGHIJ:",
            Corrector::CMD_ACK_NEW.":$makerId:",
            Corrector::CMD_REJECT.":$makerId:{$row->getHash()}:",
            Corrector::CMD_IGNORE_UNTIL.":$makerId:{$row->getHash()}:$monthLater:",
        ]);
    }

    private function reportNewPasscode(SymfonyStyle $io, Row $row): void
    {
        $io->warning("{$row->getNames()} set new passcode: {$row->getProvidedPasscode()}");
        $io->writeln("{$row->getMakerId()} {$row->getProvidedPasscode()}");
    }

    private function reportChangedMakerId(SymfonyStyle $io, Row $row): void
    {
        $io->warning("{$row->getNames()} changed their maker ID");
    }

    private function reportInvalidPasscode(SymfonyStyle $io, Row $row, $expectedPasscode): void
    {
        $io->warning("{$row->getNames()} provided invalid passcode '{$row->getProvidedPasscode()}' (expected: '$expectedPasscode')");
        $io->writeln(Corrector::CMD_IGNORE_PIN.":{$row->getMakerId()}:{$row->getHash()}:");
        $io->writeln(Corrector::CMD_REJECT.":{$row->getMakerId()}:{$row->getHash()}:");
    }
}
