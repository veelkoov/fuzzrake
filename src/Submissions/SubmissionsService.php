<?php

declare(strict_types=1);

namespace App\Submissions;

use App\DataDefinitions\Fields\Fields;
use App\DataDefinitions\Fields\FieldsList;
use App\Repository\ArtisanRepository;
use App\Utils\Arrays;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\Fixer;
use App\Utils\Data\Manager;
use App\Utils\DateTime\UtcClock;
use App\Utils\FieldReadInterface;
use App\Utils\IuSubmissions\Finder;
use App\Utils\IuSubmissions\IuSubmission;
use App\Utils\StringList;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function Psl\Iter\first;
use function Psl\Vec\concat;
use function Psl\Vec\filter;

class SubmissionsService
{
    private readonly string $submissionsDirPath;

    public function __construct(
        private readonly ArtisanRepository $artisans,
        private readonly Fixer $fixer,
        private readonly Manager $manager,
        #[Autowire('%env(resolve:SUBMISSIONS_DIR_PATH)%')]
        string $submissionsDirPath,
    ) {
        $this->submissionsDirPath = $submissionsDirPath;
    }

    /**
     * @return IuSubmission[]
     */
    public function getSubmissions(): array
    {
        return Finder::getFrom($this->submissionsDirPath, limit: 20, reverse: true);
    }

    public function getSubmissionById(string $id): ?IuSubmission
    {
        return first(filter($this->getSubmissions(), fn ($submission) => $submission->getId() === $id));
    }

    public function getUpdate(IuSubmission $submission): Update
    {
        $originalInput = new Artisan();
        $this->updateWith($originalInput, $submission, Fields::inIuForm());

        /* This bases on input before fixing. Could use some improvements. */
        $matchedArtisans = $this->getArtisans($originalInput);
        $originalArtisan = 1 === count($matchedArtisans) ? Arrays::single($matchedArtisans) : new Artisan();

        $this->handleSpecialFieldsInInput($originalInput, $originalArtisan);

        $fixedInput = $this->fixer->getFixed($originalInput);

        $updatedArtisan = clone $originalArtisan;
        $this->updateWith($updatedArtisan, $fixedInput, Fields::iuFormAffected());

        $this->handleSpecialFieldsInEntity($updatedArtisan, $originalArtisan);
        $this->manager->correctArtisan($updatedArtisan, $submission->getId());

        return new Update(
            $submission,
            $matchedArtisans,
            $originalInput,
            $originalArtisan,
            $updatedArtisan,
        );
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
