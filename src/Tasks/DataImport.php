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
use App\Utils\Import\ImportException;
use App\Utils\Import\ImportItem;
use App\Utils\Import\Manager;
use App\Utils\Import\Messaging;
use App\Utils\Import\RawImportItem;
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
        $this->messaging = new Messaging($printer, $importManager);

        $this->artisanRepository = $objectManager->getRepository(Artisan::class);

        $this->fdv = $fdv;
        $this->showAllFixCmds = $showAllFixCmds;
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
     * @param array[] $artisansData
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
                $this->messaging->reportIgnoredItem($item);
                continue;
            }

            $makerId = $item->getOriginalEntity()->getMakerId() ?: $item->getFixedInput()->getMakerId();

            if (array_key_exists($makerId, $result)) {
                $this->messaging->reportUpdatedItem($item, $result[$makerId]);
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
        $flags = FDV::SHOW_DIFF | FDV::SHOW_FIX_CMD_FOR_INVALID | ($this->showAllFixCmds ? FDV::SHOW_ALL_FIX_CMD_FOR_CHANGED : 0);

        foreach ($items as $item) {
            $this->updateArtisanWithData($item->getFixedEntity(), $item->getFixedInput(), true);

            if ($this->manager->isNewPasscode($item)) {
                $item->getFixedEntity()->setPasscode($item->getProvidedPasscode());
            }

            $this->fdv->perform($item->getEntity(), $flags, $item->getOriginalInput());

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

    private function persistImportIfValid(ImportItem $item): void
    {
        $new = $item->getFixedEntity();
        $old = $item->getOriginalEntity();
        $ok = true;

        if (null === $old->getId() && !$this->manager->isAcknowledged($item)) {
            $this->messaging->reportNewMaker($item);
            $ok = false;
        }

        if (!empty($old->getMakerId()) && $old->getMakerId() !== $new->getMakerId()) {
            $this->messaging->reportChangedMakerId($item);
        }

        if ('' === ($expectedPasscode = $item->getFixedEntity()->getPrivateData()->getPasscode())) {
            $this->messaging->reportNewPasscode($item);

            $ok = false;
        } elseif ($item->getProvidedPasscode() !== $expectedPasscode && !$this->manager->shouldIgnorePasscode($item)) {
            $this->messaging->reportInvalidPasscode($item, $expectedPasscode);

            $ok = false;
        }

        if ($ok) {
            $this->objectManager->persist($new);
        } elseif ($new->getId()) {
            $item->getEntity()->reset();
        }
    }
}
