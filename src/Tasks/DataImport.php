<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\Fields;
use App\Utils\Artisan\Utils;
use App\Utils\Data\ArtisanFixWip;
use App\Utils\Data\FixerDifferValidator as FDV;
use App\Utils\Data\Printer;
use App\Utils\FieldReadInterface;
use App\Utils\DataInput\DataInputException;
use App\Utils\DataInput\ImportItem;
use App\Utils\DataInput\Manager;
use App\Utils\DataInput\Messaging;
use App\Utils\DataInput\RawImportItem;
use App\Utils\StringList;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class DataImport
{
    /**
     * @var ObjectRepository|ArtisanRepository
     */
    private ObjectRepository $artisanRepository;

    private EntityManagerInterface $objectManager;
    private Manager $manager;
    private Printer $printer;
    private Messaging $messaging;
    private FDV $fdv;
    private bool $showAllFixCmds;

    public function __construct(
        EntityManagerInterface $objectManager,
        Manager $importManager,
        Printer $printer,
        FDV $fdv,
        bool $showAllFixCmds
    ) {
        $this->objectManager = $objectManager;
        $this->manager = $importManager;
        $this->printer = $printer;
        $this->messaging = new Messaging($printer, $importManager);

        $this->artisanRepository = $objectManager->getRepository(Artisan::class);

        $this->fdv = $fdv;
        $this->showAllFixCmds = $showAllFixCmds;
    }

    /**
     * @param array[] $artisansData
     *
     * @throws DataInputException
     */
    public function import(array $artisansData): void
    {
        $flags = FDV::SHOW_DIFF | FDV::SHOW_FIX_CMD_FOR_INVALID | ($this->showAllFixCmds ? FDV::SHOW_ALL_FIX_CMD_FOR_CHANGED : 0);

        foreach ($this->createImportItems($artisansData) as $item) {
            $this->updateArtisanWithData($item->getFixedEntity(), $item->getFixedInput(), true);

            if ($this->manager->isNewPasscode($item)) {
                $item->getFixedEntity()->setPasscode($item->getProvidedPasscode());
            }

            $item->calculateDiff();
            if ($item->getDiff()->hasAnythingChanged()) {
                $this->printer->setCurrentContext($item->getEntity());
                $this->messaging->reportUpdates($item);
            }

            $this->fdv->perform($item->getEntity(), $flags, $item->getOriginalInput());

            if ($this->checkValidEmitWarnings($item)) {
                $this->commit($item);
            }
        }
    }

    /**
     * @param array[] $artisansData
     *
     * @return ImportItem[]
     *
     * @throws DataInputException
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
                $this->messaging->reportIgnoredItem($item);
                continue;
            }

            $makerId = $item->getOriginalEntity()->getMakerId() ?: $item->getFixedInput()->getMakerId();

            if (array_key_exists($makerId, $result)) {
                $item->addReplaced($result[$makerId]);
            }

            $result[$makerId] = $item;
        }

        return $result;
    }

    /**
     * @throws DataInputException
     */
    private function createImportItem(array $artisanData): ImportItem
    {
        $raw = new RawImportItem($artisanData);

        $originalInput = $this->updateArtisanWithData(new Artisan(), $raw, false);

        $input = new ArtisanFixWip($originalInput);
        $this->manager->correctArtisan($input->getFixed());
        $this->fdv->perform($input, FDV::FIX);

        $originalEntity = $this->findBestMatchArtisan($input->getFixed()) ?: new Artisan();

        $entity = new ArtisanFixWip($originalEntity);

        return new ImportItem($raw, $input, $entity);
    }

    private function updateArtisanWithData(Artisan $artisan, FieldReadInterface $source, bool $skipPasscodeUpdate): Artisan
    {
        foreach (Fields::importedFromIuForm() as $field) {
            if ($skipPasscodeUpdate && $field->is(Fields::PASSCODE)) {
                continue;
            }

            switch ($field->name()) {
                case Fields::MAKER_ID:
                    $newValue = $source->get($field);

                    if ($newValue !== $artisan->getMakerId()) {
                        $artisan->setFormerMakerIds(StringList::pack($artisan->getAllMakerIdsArr()));
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

    private function findBestMatchArtisan(Artisan $artisan): ?Artisan
    {
        $results = $this->artisanRepository->findBestMatches(
            $artisan->getAllNamesArr(),
            $artisan->getAllMakerIdsArr(),
            $this->manager->getMatchedName($artisan->getMakerId())
        );

        if (count($results) > 1) {
            $this->messaging->reportMoreThanOneMatchedArtisans($artisan, $results);

            return null;
        }

        return array_pop($results);
    }

    private function checkValidEmitWarnings(ImportItem $item): bool
    {
        $new = $item->getFixedEntity();
        $old = $item->getOriginalEntity();

        if (null === $old->getId() && !$this->manager->isAcknowledged($item)) {
            $this->messaging->reportNewMaker($item);

            return false;
        }

        if (!empty($old->getMakerId()) && $old->getMakerId() !== $new->getMakerId()) {
            $this->messaging->reportChangedMakerId($item);
        }

        if ('' === ($expectedPasscode = $new->getPrivateData()->getPasscode())) {
            $this->messaging->reportNewPasscode($item);

            return false;
        }

        if ($item->getProvidedPasscode() !== $expectedPasscode && !$this->manager->shouldIgnorePasscode($item)) {
            $this->messaging->reportInvalidPasscode($item, $expectedPasscode);

            return false;
        }

        return true;
    }

    private function commit(ImportItem $item): void
    {
        $item->getEntity()->apply();
        $this->objectManager->persist($item->getOriginalEntity());
    }
}
