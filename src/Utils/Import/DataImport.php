<?php

declare(strict_types=1);

namespace App\Utils\Import;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\Fields;
use App\Utils\Artisan\Utils;
use App\Utils\Data\Differ;
use App\Utils\Data\Fixer;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\FieldReadInterface;
use App\Utils\StrUtils;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataImport
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
     * @var Fixer
     */
    private $fixer;

    /**
     * @var Differ
     */
    private $differ;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(ArtisanRepository $artisanRepository, ObjectManager $objectManager,
        Manager $importManager, SymfonyStyle $io, bool $showFixCommands)
    {
        $this->artisanRepository = $artisanRepository;
        $this->objectManager = $objectManager;
        $this->io = $io;

        $this->fixer = new Fixer($io, false);
        $this->differ = new Differ($io, $showFixCommands);
        $this->manager = $importManager;
    }

    /**
     * @param array[] $artisansData
     *
     * @throws ImportException
     */
    public function import(array $artisansData): void
    {
        $items = $this->createImportItems($artisansData);
        $this->processImportItems($items);
    }

    /**
     * @param ImportItem[] $artisansData
     *
     * @return ImportItem[]
     *
     * @throws ImportException
     */
    private function createImportItems(array $artisansData): array
    {
        $result = [];

        foreach ($artisansData as $artisanData) {
            $item = $this->createImportItem($artisanData);

            if ($this->manager->isRejected($item)) {
                continue;
            }

            if ($this->manager->isDelayed($item)) {
                $this->io->note("Ignoring {$item->getIdStringSafe()} until {$this->manager->getIgnoredUntilDate($item)->format('Y-m-d')}");
                continue;
            }

            $makerId = $item->getOriginalArtisan()->getMakerId() ?: $item->getFixedInput()->getMakerId();

            if (array_key_exists($makerId, $result)) {
                $this->io->note($item->getIdStringSafe().' was identified as an update to '.$result[$makerId]->getIdStringSafe());
            }

            $result[$makerId] = $item;
        }

        return $result;
    }

    /**
     * @throws ImportException
     */
    private function createImportItem(array $artisanData): ImportItem
    {
        $raw = new RawImportItem($artisanData);
        $input = $this->updateArtisanWithData(new Artisan(), $raw, false);

        $fixedInput = clone $input;
        $this->manager->correctArtisan($fixedInput);
        $this->fixer->fix($fixedInput);

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

            $this->differ->showDiff($item->getOriginalArtisan(), $item->getArtisan(), $item->getInput());

            $this->persistImportIfValid($item);
        }
    }

    private function updateArtisanWithData(Artisan $artisan, FieldReadInterface $source, bool $protectedChanges): Artisan
    {
        foreach (Fields::importedFromIuForm() as $field) {
            if ($protectedChanges && $field->is(Fields::PASSCODE)) {
                continue;
            }

            switch ($field->name()) {
                case Fields::MAKER_ID:
                    $newValue = $source->get($field);

                    if ($newValue !== $artisan->getMakerId()) {
                        $artisan->setFormerMakerIds(implode("\n", $artisan->getAllMakerIdsArr()));
                        $artisan->setMakerId($newValue);
                    }
                    break;

                case Fields::CONTACT_INPUT_VIRTUAL:
                    $newValue = $source->get($field);

                    if ($newValue === $artisan->getContactInfoObfuscated()) {
                        break; // No updates
                    }

                    Utils::updateContact($artisan, $newValue);
                    break;

                default:
                    $artisan->set($field, $source->get($field));
            }
        }

        return $artisan;
    }

    /**
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

    private function persistImportIfValid(ImportItem $item): void
    {
        $new = $item->getArtisan();
        $old = $item->getOriginalArtisan();
        $this->fixer->validateArtisanData($new);
        $ok = true;

        if (null === $old->getId() && !$this->manager->isAcknowledged($item)) {
            $this->reportNewMaker($item);
            $ok = false;
        }

        if (!empty($old->getMakerId()) && $old->getMakerId() !== $new->getMakerId()) {
            $this->reportChangedMakerId($item);
        }

        if ('' === ($expectedPasscode = $item->getArtisan()->getPrivateData()->getPasscode())) {
            $this->reportNewPasscode($item);

            $ok = false;
        } elseif ($item->getProvidedPasscode() !== $expectedPasscode && !$this->manager->shouldIgnorePasscode($item)) {
            $this->reportInvalidPasscode($item, $expectedPasscode);

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
        return 'Was looking for: '.StrUtils::artisanNamesSafeForCli($artisan).'. Found more than one: '
            .implode(', ', array_map(function (Artisan $artisan) {
                return StrUtils::artisanNamesSafeForCli($artisan);
            }, $results));
    }

    private function reportNewMaker(ImportItem $item): void
    {
        $monthLater = DateTimeUtils::getMonthLaterYmd();
        $makerId = $item->getMakerId();

        $this->io->warning("New maker: {$item->getNames()}");
        $this->io->writeln([
            Manager::CMD_MATCH_NAME.":$makerId:ABCDEFGHIJ:",
            Manager::CMD_ACK_NEW.":$makerId:",
            Manager::CMD_REJECT.":$makerId:{$item->getHash()}:",
            Manager::CMD_IGNORE_UNTIL.":$makerId:{$item->getHash()}:$monthLater:",
        ]);
    }

    private function reportChangedMakerId(ImportItem $item): void
    {
        $this->io->warning($item->getNames().' changed their maker ID from '.$item->getOriginalArtisan()->getMakerId()
            .' to '.$item->getArtisan()->getMakerId());
    }

    private function reportNewPasscode(ImportItem $item): void
    {
        $this->io->warning("{$item->getNames()} set new passcode: {$item->getProvidedPasscode()}");
        $this->io->writeln(Manager::CMD_SET_PIN.":{$item->getMakerId()}:{$item->getHash()}:");
    }

    private function reportInvalidPasscode(ImportItem $item, string $expectedPasscode): void
    {
        $weekLater = DateTimeUtils::getWeekLaterYmd();
        $makerId = $item->getMakerId();
        $hash = $item->getHash();

        $this->io->warning("{$item->getNames()} provided invalid passcode '{$item->getProvidedPasscode()}' (expected: '$expectedPasscode')");
        $this->io->writeln([
            Manager::CMD_IGNORE_PIN.":$makerId:$hash:",
            Manager::CMD_REJECT.":$makerId:$hash:",
            Manager::CMD_SET_PIN.":$makerId:$hash:",
            Manager::CMD_IGNORE_UNTIL.":$makerId:$hash:$weekLater:",
        ]);
    }
}
