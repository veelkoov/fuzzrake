<?php

declare(strict_types=1);

namespace App\Submissions;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\Fields;
use App\Repository\ArtisanRepository;
use App\Utils\Arrays;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\Fixer;
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
        $this->updateWith($originalInput, $submission);

        /* This bases on input before fixing. Could use some improvements. */
        $matchedArtisans = $this->getArtisans($originalInput);
        $originalArtisan = 1 === count($matchedArtisans) ? Arrays::single($matchedArtisans) : new Artisan();

        $this->handleSpecialFieldsInInput($originalInput, $originalArtisan);

        $fixedInput = $this->fixer->getFixed($originalInput);

        $updatedArtisan = clone $originalArtisan;
        $this->updateWith($updatedArtisan, $fixedInput);

        $this->handleSpecialFieldsInEntity($updatedArtisan, $originalArtisan);

        return new Update(
            $submission,
            $matchedArtisans,
            $originalInput,
            $originalArtisan,
            $updatedArtisan,
        );
    }

    public function updateWith(Artisan $artisan, FieldReadInterface $source): void
    {
        foreach (Fields::inIuForm() as $field) {
            switch ($field) {
                case Field::MAKER_ID:
                    $newValue = $source->getString($field);

                    if ($newValue !== $artisan->getMakerId()) {
                        $artisan->setMakerId($newValue);
                        $artisan->setFormerMakerIds(StringList::pack($artisan->getAllMakerIdsArr()));
                    } else {
                        $artisan->setFormerMakerIds($source->getString(Field::FORMER_MAKER_IDS));
                    }
                    break;

                case Field::CONTACT_INFO_OBFUSCATED:
                    $artisan->set(Field::CONTACT_INFO_OBFUSCATED, $source->get(Field::CONTACT_INFO_OBFUSCATED));
                    $artisan->set(Field::CONTACT_INFO_ORIGINAL, $source->get(Field::CONTACT_INFO_ORIGINAL));
                    $artisan->set(Field::CONTACT_METHOD, $source->get(Field::CONTACT_METHOD));
                    $artisan->set(Field::CONTACT_ADDRESS_PLAIN, $source->get(Field::CONTACT_ADDRESS_PLAIN));
                    break;

                case Field::URL_PHOTOS:
                    // Known limitation: unable to easily reorder photos grep-cannot-easily-reorder-photos
                    if (!StringList::sameElements($artisan->getString($field), $source->getString($field))) {
                        $artisan->setMiniatureUrls('');
                    }

                    $artisan->set($field, $source->get($field));
                    break;

                default:
                    $artisan->set($field, $source->get($field));
            }
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
        }
    }

    private function handleSpecialFieldsInEntity(Artisan $updatedArtisan, Artisan $originalArtisan): void
    {
        if (!StringList::sameElements($updatedArtisan->getPhotoUrls(), $originalArtisan->getPhotoUrls())) {
            $updatedArtisan->setMiniatureUrls('');
        }
    }
}
