<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\ArtisanFields as Fields;
use App\Utils\ArtisanUtils;
use App\Utils\DataDiffer;
use App\Utils\DataFixer;
use App\Utils\DateTimeUtils;
use App\Utils\FieldReadInterface;
use App\Utils\Import\ImportException;
use App\Utils\Import\ImportItem;
use App\Utils\Import\Manager;
use App\Utils\Import\RawImportItem;
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
     * @var Manager
     */
    private $manager;

    public function __construct(ArtisanRepository $artisanRepository, ObjectManager $objectManager)
    {
        $this->artisanRepository = $artisanRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * @param array        $artisansData
     * @param Manager      $importManager
     * @param SymfonyStyle $io
     * @param bool         $showFixCommands
     *
     * @throws ImportException
     */
    public function import(array $artisansData, Manager $importManager, SymfonyStyle $io, bool $showFixCommands): void
    {
        $this->fixer = new DataFixer($io, false);
        $this->differ = new DataDiffer($io, $showFixCommands);
        $this->manager = $importManager;

        $items = $this->createImportItems($artisansData, $io);
        $this->processImportItems($items);

        $io->title('Showing artisans\' data before/after fixing');
        $this->showUpdatedArtisans($items);

        $io->title('Validating updated artisans\' data and passcodes');
        $this->commitValidImportItems($items, $io);
    }

    /**
     * @param array        $artisansData
     * @param SymfonyStyle $io
     *
     * @return ImportItem[]
     *
     * @throws ImportException
     */
    private function createImportItems(array $artisansData, SymfonyStyle $io): array
    {
        $result = [];

        foreach ($artisansData as $artisanData) {
            $item = $this->createImportItem($artisanData);

            if ($this->manager->isRejected($item)) {
                continue;
            }

            if ($this->manager->isDelayed($item)) {
                $io->note("Ignoring {$item->getIdStringSafe()} until {$this->manager->getIgnoredUntilDate($item)->format('Y-m-d')}");
                continue;
            }

            $makerId = $item->getOriginalArtisan()->getMakerId() ?: $item->getFixedInput()->getMakerId();

            if (array_key_exists($makerId, $result)) {
                $io->note($item->getIdStringSafe().' was identified as an update to '.$result[$makerId]->getIdStringSafe());
            }

            $result[$makerId] = $item;
        }

        return $result;
    }

    /**
     * @param array $artisanData
     *
     * @return ImportItem
     *
     * @throws ImportException
     */
    private function createImportItem(array $artisanData): ImportItem
    {
        $raw = new RawImportItem($artisanData);
        $input = $this->updateArtisanWithData(new Artisan(), $raw, false);

        $fixedInput = clone $input;
        $this->manager->correctArtisan($fixedInput);
        $this->fixer->fixArtisanData($fixedInput);

        $artisan = $this->findBestMatchArtisan($fixedInput) ?: new Artisan();
        $originalArtisan = clone $artisan; // Clone unmodified

        return new ImportItem($raw, $input, $fixedInput, $originalArtisan, $artisan);
    }

    /**
     * @param ImportItem[] $items
     */
    private function processImportItems(array $items): void
    {
        foreach ($items as $item) {
            $this->updateArtisanWithData($item->getArtisan(), $item->getFixedInput(), true);

            if ($this->manager->isNewPasscode($item)) {
                $item->getArtisan()->setPasscode($item->getProvidedPasscode());
            }
        }
    }

    private function updateArtisanWithData(Artisan $artisan, FieldReadInterface $source, bool $protectedChanges): Artisan
    {
        foreach (Fields::inIuForm() as $field) {
            if ($protectedChanges && $field->is(Fields::PASSCODE)) {
                continue;
            }

            switch ($field->name()) {
                case Fields::IGNORED_IU_FORM_FIELD:
                case Fields::TIMESTAMP:
                    break;

                case Fields::MAKER_ID:
                    $newValue = $source->get($field);

                    if ($newValue !== $artisan->getMakerId()) {
                        $artisan->setFormerMakerIds(implode("\n", $artisan->getAllMakerIdsArr()));
                        $artisan->setMakerId($newValue);
                    }
                    break;

                case Fields::CONTACT_INPUT_VIRTUAL:
                    $newValue = $source->get($field);

                    if ($newValue === $artisan->getContactAddressObfuscated()) {
                        break; // No updates
                    }

                    ArtisanUtils::updateContact($artisan, $newValue);
                    break;

                default:

                    $artisan->set($field, $source->get($field));
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
        $results = $this->artisanRepository->findBestMatches(
            $artisan->getAllNamesArr(),
            $artisan->getAllMakerIdsArr(),
            $this->manager->getMatchedName($artisan->getMakerId())
        );

        if (count($results) > 1) {
            throw new ImportException($this->getMoreThanOneArtisansMatchedMessage($artisan, $results));
        }

        return array_pop($results);
    }

    private function showUpdatedArtisans(array $rows): void
    {
        foreach ($rows as $row) {
            $this->differ->showDiff($row->getOriginalArtisan(), $row->getArtisan(), $row->getInput());
        }
    }

    private function commitValidImportItems(array $imports, SymfonyStyle $io): void
    {
        foreach ($imports as $import) {
            $this->persistImportIfValid($io, $import);
        }
    }

    private function persistImportIfValid(SymfonyStyle $io, ImportItem $item): void
    {
        $new = $item->getArtisan();
        $old = $item->getOriginalArtisan();
        $this->fixer->validateArtisanData($new);
        $ok = true;

        if (null === $old->getId() && !$this->manager->isAcknowledged($item)) {
            $this->reportNewMaker($io, $item);
            $ok = false;
        }

        if (!empty($old->getMakerId()) && $old->getMakerId() !== $new->getMakerId()) {
            $this->reportChangedMakerId($io, $item);
        }

        if ('' === ($expectedPasscode = $item->getArtisan()->getPrivateData()->getPasscode())) {
            $this->reportNewPasscode($io, $item);

            $ok = false;
        } elseif ($item->getProvidedPasscode() !== $expectedPasscode && !$this->manager->shouldIgnorePasscode($item)) {
            $this->reportInvalidPasscode($io, $item, $expectedPasscode);

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

    private function reportNewMaker(SymfonyStyle $io, ImportItem $item): void
    {
        $monthLater = DateTimeUtils::getMonthLaterYmd();
        $makerId = $item->getMakerId();

        $io->warning("New maker: {$item->getNames()}");
        $io->writeln([
            Manager::CMD_MATCH_NAME.":$makerId:ABCDEFGHIJ:",
            Manager::CMD_ACK_NEW.":$makerId:",
            Manager::CMD_REJECT.":$makerId:{$item->getHash()}:",
            Manager::CMD_IGNORE_UNTIL.":$makerId:{$item->getHash()}:$monthLater:",
        ]);
    }

    private function reportChangedMakerId(SymfonyStyle $io, ImportItem $item): void
    {
        $io->warning($item->getNames().' changed their maker ID from '.$item->getOriginalArtisan()->getMakerId()
            .' to '.$item->getArtisan()->getMakerId());
    }

    private function reportNewPasscode(SymfonyStyle $io, ImportItem $item): void
    {
        $io->warning("{$item->getNames()} set new passcode: {$item->getProvidedPasscode()}");
        $io->writeln(Manager::CMD_SET_PIN.":{$item->getMakerId()}:{$item->getHash()}:");
    }

    private function reportInvalidPasscode(SymfonyStyle $io, ImportItem $item, string $expectedPasscode): void
    {
        $io->warning("{$item->getNames()} provided invalid passcode '{$item->getProvidedPasscode()}' (expected: '$expectedPasscode')");
        $io->writeln([
            Manager::CMD_IGNORE_PIN.":{$item->getMakerId()}:{$item->getHash()}:",
            Manager::CMD_REJECT.":{$item->getMakerId()}:{$item->getHash()}:",
            Manager::CMD_SET_PIN.":{$item->getMakerId()}:{$item->getHash()}:",
        ]);
    }
}
