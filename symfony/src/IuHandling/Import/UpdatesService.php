<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\Fields\Fields;
use App\Data\Definitions\Fields\FieldsList;
use App\Data\Fixer\Fixer;
use App\Entity\Submission;
use App\IuHandling\Exception\ManagerConfigError;
use App\Repository\ArtisanRepository;
use App\Utils\Arrays\Arrays;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DateTime\UtcClock;
use App\Utils\FieldReadInterface;
use App\Utils\StringList;
use App\Utils\UnbelievableRuntimeException;

use function Psl\Vec\concat;
use function Psl\Vec\filter;

class UpdatesService
{
    public function __construct(
        private readonly ArtisanRepository $artisans,
        private readonly Fixer $fixer,
    ) {
    }

    public function getUpdateFor(UpdateInput $input): Update
    {
        [$directivesError, $manager] = $this->getManager($input->submission);
        $errors = filter([$directivesError]);

        $originalInput = new Artisan();
        $this->updateWith($originalInput, $input->submissionData, Fields::readFromSubmissionData());

        $fixedInput = $this->fixer->getFixed($originalInput);

        $matchedArtisans = $this->getArtisans($fixedInput, $manager->getMatchedMakerId());

        if (1 === count($matchedArtisans)) {
            $originalArtisan = Arrays::single($matchedArtisans);
        } else {
            $originalArtisan = new Artisan();

            if ([] !== $matchedArtisans) {
                $errors[] = 'Single maker must get selected.';
            }
        }

        // if (!$isContactAllowed) {
        //     $state->artisan->setEmailAddressObfuscated('');
        // } TODO

        $this->handleSpecialFieldsInInput($fixedInput, $originalArtisan);

        $updatedArtisan = clone $originalArtisan;
        $this->updateWith($updatedArtisan, $fixedInput, Fields::iuFormAffected());

        $this->handleSpecialFieldsInEntity($updatedArtisan, $originalArtisan);
        $manager->correctArtisan($updatedArtisan);

        $isNew = null === $originalArtisan->getId();
        $isAccepted = $manager->isAccepted();

        if (!$isNew && $originalArtisan->getPassword() !== $updatedArtisan->getPassword() && !$isAccepted) {
            $errors[] = 'Password does not match.';
        }

        return new Update(
            $input->submissionData,
            $input->submission,
            $matchedArtisans,
            $originalInput,
            $originalArtisan,
            $updatedArtisan,
            $errors,
            $isAccepted,
            $isNew,
        );
    }

    /**
     * @return array{0: string, 1: Manager}
     */
    private function getManager(Submission $submission): array
    {
        try {
            return ['', new Manager($submission->getDirectives())];
        } catch (ManagerConfigError $error) {
            $directivesError = "The directives have been ignored completely due to an error. {$error->getMessage()}";

            try {
                return [$directivesError, new Manager('')];
            } catch (ManagerConfigError $error) { // @codeCoverageIgnoreStart
                throw new UnbelievableRuntimeException($error);
            } // @codeCoverageIgnoreEnd
        }
    }

    private function updateWith(Artisan $artisan, FieldReadInterface $source, FieldsList $fields): void
    {
        foreach ($fields as $field) {
            $artisan->set($field, $source->get($field));
        }

        $artisan->assureNsfwSafety();
    }

    /**
     * @return Artisan[]
     */
    private function getArtisans(Artisan $submissionData, ?string $matchedMakerId): array
    {
        if (null !== $matchedMakerId) {
            $makerIds = [$matchedMakerId];
            $names = [];
        } else {
            $makerIds = $submissionData->getAllMakerIds();
            $names = concat([$submissionData->getName()], $submissionData->getFormerly());
        }

        $results = $this->artisans->findBestMatches($names, $makerIds);

        return Artisan::wrapAll($results);
    }

    private function handleSpecialFieldsInInput(Artisan $originalInput, Artisan $originalArtisan): void
    {
        $submittedContact = $originalInput->getEmailAddressObfuscated();

        if (null !== $originalArtisan->getId() && $submittedContact === $originalArtisan->getEmailAddressObfuscated()) {
            $originalInput->updateEmailAddress($originalArtisan->getEmailAddress());
        } else {
            $originalInput->updateEmailAddress($submittedContact);
        }

        if (null === $originalArtisan->getId()) {
            $originalInput->setDateAdded(UtcClock::now());
        } else {
            $originalInput->setDateAdded($originalArtisan->getDateAdded());
            $originalInput->setDateUpdated(UtcClock::now());

            if ($originalInput->getMakerId() !== $originalArtisan->getMakerId()) {
                $originalInput->setFormerMakerIds($originalArtisan->getAllMakerIds());
            } else {
                $originalInput->setFormerMakerIds($originalArtisan->getFormerMakerIds());
            }
        }
    }

    private function handleSpecialFieldsInEntity(Artisan $updatedArtisan, Artisan $originalArtisan): void
    {
        // Known limitation: unable to easily reorder photos grep-cannot-easily-reorder-photos
        if (!StringList::sameElements($updatedArtisan->getPhotoUrls(), $originalArtisan->getPhotoUrls())) {
            $updatedArtisan->setMiniatureUrls([]); // FIXME: https://github.com/veelkoov/fuzzrake/issues/160
        }
    }

    public function import(Update $update): void
    {
        $existingEntity = $update->originalArtisan;
        $cloneWithUpdates = $update->updatedArtisan;

        foreach (Fields::persisted() as $field) {
            $existingEntity->set($field, $cloneWithUpdates->get($field));
        }

        $this->artisans->add($existingEntity->getArtisan(), true);
    }
}
