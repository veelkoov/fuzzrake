<?php

declare(strict_types=1);

namespace App\Submissions;

use App\DataDefinitions\Fields\Fields;
use App\DataDefinitions\Fields\FieldsList;
use App\Entity\Submission;
use App\Repository\ArtisanRepository;
use App\Utils\Arrays;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\Fixer;
use App\Utils\DateTime\UtcClock;
use App\Utils\FieldReadInterface;
use App\Utils\StringList;
use App\Utils\UnbelievableRuntimeException;
use Psr\Log\LoggerInterface;

use function Psl\Vec\concat;
use function Psl\Vec\filter;

class UpdatesService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ArtisanRepository $artisans,
        private readonly SubmissionsService $submissions,
        private readonly Fixer $fixer,
    ) {
    }

    /**
     * @throws MissingSubmissionException
     */
    public function getUpdateBySubmissionId(string $submissionId): Update
    {
        $submissionData = $this->submissions->getSubmissionDataById($submissionId);
        $submission = $this->submissions->getSubmissionById($submissionData->getId());

        return $this->getUpdateFor($submissionData, $submission);
    }

    public function getUpdateFor(SubmissionData $submissionData, Submission $submission): Update
    {
        [$directivesError, $manager] = $this->getManager($submission);

        $originalInput = new Artisan();
        $this->updateWith($originalInput, $submissionData, Fields::inIuForm());

        /* This bases on input before fixing. Could use some improvements. */
        $matchedArtisans = $this->getArtisans($originalInput);
        $originalArtisan = 1 === count($matchedArtisans) ? Arrays::single($matchedArtisans) : new Artisan();

        $this->handleSpecialFieldsInInput($originalInput, $originalArtisan);

        $fixedInput = $this->fixer->getFixed($originalInput);

        $updatedArtisan = clone $originalArtisan;
        $this->updateWith($updatedArtisan, $fixedInput, Fields::iuFormAffected());

        $this->handleSpecialFieldsInEntity($updatedArtisan, $originalArtisan);
        $manager->correctArtisan($updatedArtisan, $submissionData->getId());

        return new Update(
            $submissionData,
            $submission,
            $matchedArtisans,
            $originalInput,
            $originalArtisan,
            $updatedArtisan,
            filter([$directivesError]),
        );
    }

    /**
     * @return array{0: string, 1: Manager}
     */
    public function getManager(Submission $submission): array
    {
        $directives = $submission->getDirectives();
        $strId = $submission->getStrId();

        try {
            return ['', new Manager($this->logger, "with {$strId}:\n$directives")]; // TODO: Remove "with"
        } catch (ManagerConfigError $error) {
            $directivesError = "The directives have been ignored completely due to an error. {$error->getMessage()}";

            try {
                return [$directivesError, new Manager($this->logger, '')];
            } catch (ManagerConfigError $error) {
                throw new UnbelievableRuntimeException($error);
            }
        }
    }

    public function updateWith(Artisan $artisan, FieldReadInterface $source, FieldsList $fields): void
    {
        foreach ($fields as $field) {
            $artisan->set($field, $source->get($field));
        }

        $artisan->assureNsfwSafety();
    }

    /**
     * @return Artisan[]
     */
    private function getArtisans(Artisan $submissionData): array
    {
        $results = $this->artisans->findBestMatches(
            concat([$submissionData->getName()], $submissionData->getFormerlyArr()),
            concat([$submissionData->getMakerId()], $submissionData->getFormerMakerIdsArr()),
            null, // TODO: Remove this or implement
        );

        return Artisan::wrapAll($results);
    }

    private function handleSpecialFieldsInInput(Artisan $originalInput, Artisan $originalArtisan): void
    {
        $submittedContact = $originalInput->getContactInfoObfuscated();

        if (null !== $originalArtisan->getId() && $submittedContact === $originalArtisan->getContactInfoObfuscated()) {
            $originalInput->updateContact($originalArtisan->getContactInfoOriginal());
        } else {
            $originalInput->updateContact($submittedContact);
        }

        if (null === $originalArtisan->getId()) {
            $originalInput->setDateAdded(UtcClock::now());
        } else {
            $originalInput->setDateUpdated(UtcClock::now());

            if ($originalInput->getMakerId() !== $originalArtisan->getMakerId()) { // TODO: Test me!
                $originalInput->setFormerMakerIds(StringList::pack($originalArtisan->getAllMakerIdsArr()));
            } else {
                $originalInput->setFormerMakerIds($originalArtisan->getFormerMakerIds());
            }
        }
    }

    private function handleSpecialFieldsInEntity(Artisan $updatedArtisan, Artisan $originalArtisan): void
    {
        if (!StringList::sameElements($updatedArtisan->getPhotoUrls(), $originalArtisan->getPhotoUrls())) {
            $updatedArtisan->setMiniatureUrls('');
        }
    }
}