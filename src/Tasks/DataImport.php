<?php

declare(strict_types=1);

namespace App\Tasks;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\Fields;
use App\Entity\Artisan as ArtisanE;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Data\FixerDifferValidator as FDV;
use App\Utils\Data\Manager;
use App\Utils\Data\Printer;
use App\Utils\DataInputException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\FieldReadInterface;
use App\Utils\IuSubmissions\ImportItem;
use App\Utils\IuSubmissions\IuSubmission;
use App\Utils\IuSubmissions\Messaging;
use App\Utils\StringList;
use Doctrine\ORM\EntityManagerInterface;

class DataImport
{
    private readonly ArtisanRepository $artisanRepository;
    private readonly Messaging $messaging;

    public function __construct(
        private readonly EntityManagerInterface $objectManager,
        private readonly Manager $manager,
        private readonly Printer $printer,
        private readonly FDV $fdv,
        private readonly bool $showAllFixCmds,
    ) {
        $this->messaging = new Messaging($printer, $manager);

        $this->artisanRepository = $objectManager->getRepository(ArtisanE::class);
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
                $item->addReplaced($result[$makerId]->getIdStrSafe());

                foreach ($result[$makerId]->getReplaced() as $previous) {
                    $item->addReplaced($previous);
                }
            }

            $result[$makerId] = $item;
        }

        return $result;
    }

    /**
     * @throws DataInputException
     */
    private function createImportItem(IuSubmission $submission): ImportItem
    {
        $originalInput = $this->updateArtisanWithData(new Artisan(), $submission);

        $input = new ArtisanChanges($originalInput, $submission->getId());
        $this->manager->correctArtisan($input->getChanged(), $submission->getId());
        $this->fdv->perform($input, FDV::FIX);

        $originalEntity = $this->findBestMatchArtisan($submission, $input->getChanged()) ?: new Artisan();

        $entity = new ArtisanChanges($originalEntity, $submission->getId());

        return new ImportItem($submission, $input, $entity);
    }

    private function updateArtisanWithData(Artisan $artisan, FieldReadInterface $source): Artisan
    {
        foreach (Fields::inIuForm() as $field) {
            switch ($field) {
                case Field::MAKER_ID:
                    $newValue = $source->get($field);

                    if ($newValue !== $artisan->getMakerId()) {
                        $artisan->setFormerMakerIds(StringList::pack($artisan->getAllMakerIdsArr()));
                        $artisan->setMakerId($newValue);
                    }
                    break;

                case Field::CONTACT_INFO_OBFUSCATED: // grep-contact-updates-magic
                    $newValue = $source->get(Field::CONTACT_INFO_ORIGINAL);

                    if ($newValue === $artisan->getContactInfoObfuscated()) {
                        break; // No updates
                    }

                    $artisan->updateContact($newValue);
                    break;

                case Field::URL_PHOTOS:
                    // Known limitation: unable to easily reorder photos grep-cannot-easily-reorder-photos
                    if (!StringList::sameElements($artisan->get($field), $source->get($field))) {
                        $artisan->setMiniatureUrls('');
                    }

                    $artisan->set($field, $source->get($field));
                    break;

                default:
                    $artisan->set($field, $source->get($field));
            }
        }

        $artisan->assureNsfwSafety();

        if (null === $artisan->getId()) {
            $artisan->setDateAdded(DateTimeUtils::getNowUtc());
        } else {
            $artisan->setDateUpdated(DateTimeUtils::getNowUtc());
        }

        return $artisan;
    }

    private function findBestMatchArtisan(IuSubmission $submission, Artisan $input): ?Artisan
    {
        $results = $this->artisanRepository->findBestMatches(
            array_merge([$submission->get(Field::NAME)], StringList::unpack($submission->get(Field::FORMERLY))),
            array_merge([$submission->get(Field::MAKER_ID)], StringList::unpack($submission->get(Field::FORMER_MAKER_IDS))),
            $this->manager->getMatchedName($submission->getId())
        );

        if (count($results) > 1) {
            $this->messaging->reportMoreThanOneMatchedArtisans($input, Artisan::wrapAll($results));

            return null;
        }

        return new Artisan(array_pop($results));
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

        if (!$this->manager->isAccepted($item)) {
            $this->messaging->reportNotAccepted($item);

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
