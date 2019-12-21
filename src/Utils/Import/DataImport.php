<?php

declare(strict_types=1);

namespace App\Utils\Import;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\Fields;
use App\Utils\Artisan\Utils;
use App\Utils\Data\ArtisanFixWip;
use App\Utils\Data\FixerDifferValidator as FDV;
use App\Utils\Data\Printer;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\FieldReadInterface;
use App\Utils\StrUtils;
use Doctrine\Common\Persistence\ObjectManager;

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
     * @var Manager
     */
    private $manager;

    /**
     * @var Printer
     */
    private $printer;

    /**
     * @var FDV
     */
    private $fdv;

    public function __construct(ObjectManager $objectManager, Manager $importManager, Printer $printer, FDV $fdv)
    {
        $this->objectManager = $objectManager;
        $this->manager = $importManager;
        $this->printer = $printer;

        $this->artisanRepository = $objectManager->getRepository(Artisan::class);

        $this->fdv = $fdv;
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
                $this->printer->note("Ignoring {$item->getIdStrSafe()} until {$this->manager->getIgnoredUntilDate($item)->format('Y-m-d')}");
                continue;
            }

            $makerId = $item->getOriginalEntity()->getMakerId() ?: $item->getFixedInput()->getMakerId();

            if (array_key_exists($makerId, $result)) {
                $this->printer->note($item->getIdStrSafe().' was identified as an update to '.$result[$makerId]->getIdStrSafe());
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

        $originalInput = $this->updateArtisanWithData(new Artisan(), $raw, false);

        $input = new ArtisanFixWip($originalInput, $this->objectManager);
        $this->manager->correctArtisan($input->getFixed());
        $this->fdv->perform($input, FDV::FIX);

        $originalEntity = $this->findBestMatchArtisan($input->getFixed()) ?: new Artisan();

        $entity = new ArtisanFixWip($originalEntity, $this->objectManager);

        return new ImportItem($raw, $input, $entity);
    }

    /**
     * @param ImportItem[] $items
     */
    private function processImportItems(array $items): void
    {
        foreach ($items as $item) {
            $this->updateArtisanWithData($item->getFixedEntity(), $item->getFixedInput(), true);

            if ($this->manager->isNewPasscode($item)) {
                $item->getFixedEntity()->setPasscode($item->getProvidedPasscode());
            }

            $this->fdv->perform($item->getEntity(), FDV::SHOW_DIFF | FDV::SHOW_ALL_FIX_CMD, $item->getOriginalInput());

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
        $new = $item->getFixedEntity();
        $old = $item->getOriginalEntity();
        $ok = true;

        if (null === $old->getId() && !$this->manager->isAcknowledged($item)) {
            $this->reportNewMaker($item);
            $ok = false;
        }

        if (!empty($old->getMakerId()) && $old->getMakerId() !== $new->getMakerId()) {
            $this->reportChangedMakerId($item);
        }

        if ('' === ($expectedPasscode = $item->getFixedEntity()->getPrivateData()->getPasscode())) {
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

        $this->printer->warning("New maker: {$item->getNamesStrSafe()}");
        $this->printer->writeln([
            Manager::CMD_MATCH_NAME.":$makerId:ABCDEFGHIJ:",
            Manager::CMD_ACK_NEW.":$makerId:",
            Manager::CMD_REJECT.":$makerId:{$item->getHash()}:",
            Manager::CMD_IGNORE_UNTIL.":$makerId:{$item->getHash()}:$monthLater:",
        ]);
    }

    private function reportChangedMakerId(ImportItem $item): void
    {
        $this->printer->warning($item->getNamesStrSafe().' changed their maker ID from '.$item->getOriginalEntity()->getMakerId()
            .' to '.$item->getFixedEntity()->getMakerId());
    }

    private function reportNewPasscode(ImportItem $item): void
    {
        $this->printer->warning("{$item->getNamesStrSafe()} set new passcode: {$item->getProvidedPasscode()}");
        $this->printer->writeln(Manager::CMD_SET_PIN.":{$item->getMakerId()}:{$item->getHash()}:");
    }

    private function reportInvalidPasscode(ImportItem $item, string $expectedPasscode): void
    {
        $weekLater = DateTimeUtils::getWeekLaterYmd();
        $makerId = $item->getMakerId();
        $hash = $item->getHash();

        $this->printer->warning("{$item->getNamesStrSafe()} provided invalid passcode '{$item->getProvidedPasscode()}' (expected: '$expectedPasscode')");
        $this->printer->writeln([
            Manager::CMD_IGNORE_PIN.":$makerId:$hash:",
            Manager::CMD_REJECT.":$makerId:$hash:",
            Manager::CMD_SET_PIN.":$makerId:$hash:",
            Manager::CMD_IGNORE_UNTIL.":$makerId:$hash:$weekLater:",
        ]);
    }
}
