<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\Fields;
use App\Utils\Artisan\Utils;
use App\Utils\Data\ArtisanFixWip;
use App\Utils\Data\FixerDifferValidator as FDV;
use App\Utils\Data\Manager;
use App\Utils\Data\Printer;
use App\Utils\DataInputException;
use App\Utils\FieldReadInterface;
use App\Utils\IuSubmissions\ImportItem;
use App\Utils\IuSubmissions\IuSubmission;
use App\Utils\IuSubmissions\Messaging;
use App\Utils\StringList;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class DataImport
{
    private ObjectRepository | ArtisanRepository $artisanRepository;
    private Messaging $messaging;

    public function __construct(
        private EntityManagerInterface $objectManager,
        private Manager $manager,
        private Printer $printer,
        private FDV $fdv,
        private bool $showAllFixCmds,
    ) {
        $this->messaging = new Messaging($printer, $manager);

        $this->artisanRepository = $objectManager->getRepository(Artisan::class);
    }

    /**
     * @param IuSubmission[] $artisansData
     *
     * @throws DataInputException
     */
    public function import(array $artisansData): void
    {
        $flags = FDV::SHOW_DIFF | FDV::SHOW_FIX_CMD_FOR_INVALID | FDV::USE_SET_FOR_FIX_CMD
                | ($this->showAllFixCmds ? FDV::SHOW_ALL_FIX_CMD_FOR_CHANGED : 0);

        foreach ($this->createImportItems($artisansData) as $item) {
            $this->updateArtisanWithData($item->getFixedEntity(), $item->getFixedInput());

            $item->calculateDiff();
            if ($item->getDiff()->hasAnythingChanged()) {
                $this->printer->setCurrentContext($item->getEntity());
                $this->messaging->reportUpdates($item);
            }

            $this->fdv->perform($item->getEntity(), $flags, $item->getOriginalInput());

            if ($this->checkValidEmitWarnings($item)) {
                $this->messaging->reportValid($item);
                $this->commit($item);
            }
        }
    }

    /**
     * @param IuSubmission[] $artisansData
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
     * @noinspection PhpDocRedundantThrowsInspection FIXME: throws declaration on implementation instead of interface
     */
    private function createImportItem(IuSubmission $submission): ImportItem
    {
        $originalInput = $this->updateArtisanWithData(new Artisan(), $submission);

        $input = new ArtisanFixWip($originalInput, $submission->getId());
        $this->manager->correctArtisan($input->getFixed(), $submission->getId());
        $this->fdv->perform($input, FDV::FIX);

        $originalEntity = $this->findBestMatchArtisan($input->getFixed(), $submission->getId()) ?: new Artisan();

        $entity = new ArtisanFixWip($originalEntity, $submission->getId());

        return new ImportItem($submission, $input, $entity);
    }

    private function updateArtisanWithData(Artisan $artisan, FieldReadInterface $source): Artisan
    {
        foreach (Fields::getAll() as $field) {
            switch ($field->name()) {
                case Fields::MAKER_ID:
                    $newValue = $source->get($field);

                    if ($newValue !== $artisan->getMakerId()) {
                        $artisan->setFormerMakerIds(StringList::pack($artisan->getAllMakerIdsArr()));
                        $artisan->setMakerId($newValue);
                    }
                    break;

                case Fields::CONTACT_INFO_ORIGINAL:
                    $newValue = $source->get($field);

                    if ($newValue === $artisan->getContactInfoObfuscated()) {
                        break; // No updates
                    }

                    Utils::updateContact($artisan, $newValue);
                    break;

                case Fields::URL_PHOTOS:
                    if ($artisan->get($field) !== $source->get($field)) {
                        $artisan->setMiniatureUrls('');
                    }

                    $artisan->set($field, $source->get($field));
                    break;

                case Fields::URL_MINIATURES:
                case Fields::COMMISSIONS_STATUS:
                case Fields::CST_LAST_CHECK:
                case Fields::COMPLETENESS:
                case Fields::CONTACT_METHOD:
                case Fields::CONTACT_ADDRESS_PLAIN:
                case Fields::CONTACT_INFO_OBFUSCATED:
                case Fields::FORMER_MAKER_IDS:
                    break;

                default:
                    $artisan->set($field, $source->get($field));
            }
        }

        return $artisan;
    }

    private function findBestMatchArtisan(Artisan $artisan, string $submissionId): ?Artisan
    {
        $results = $this->artisanRepository->findBestMatches(
            $artisan->getAllNamesArr(),
            $artisan->getAllMakerIdsArr(),
            $this->manager->getMatchedName($submissionId)
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
        $passwordChanged = $item->getProvidedPassword() !== $item->getExpectedPassword();

        if (null === $old->getId() && !$this->manager->isAccepted($item)) {
            $this->messaging->reportNewMaker($item);

            return false;
        }

        if (!empty($old->getMakerId()) && $old->getMakerId() !== $new->getMakerId()) {
            $this->messaging->reportChangedMakerId($item);
        }

        if ($passwordChanged && !$this->manager->isAccepted($item)) {
            $this->messaging->reportInvalidPassword($item);

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
