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
        $fixedInput = $this->fixer->getFixed($originalInput);

        $matchedArtisans = $this->getArtisans($fixedInput);

        $originalArtisan = 1 === count($matchedArtisans) ? Arrays::single($matchedArtisans) : new Artisan();

        $this->updateContact($originalInput, $fixedInput, $originalArtisan);

        $updatedArtisan = clone $originalArtisan;
        $this->updateWith($updatedArtisan, $fixedInput);
        $this->setAddedUpdatedTimestamps($updatedArtisan);

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
                        $artisan->setFormerMakerIds(StringList::pack($artisan->getAllMakerIdsArr()));
                        $artisan->setMakerId($newValue);
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
    public function getArtisans(Artisan $submissionData): array
    {
        $results = $this->artisans->findBestMatches(
            concat([$submissionData->getString(Field::NAME)], StringList::unpack($submissionData->getString(Field::FORMERLY))),
            concat([$submissionData->getString(Field::MAKER_ID)], StringList::unpack($submissionData->getString(Field::FORMER_MAKER_IDS))),
            null,
        );

        return Artisan::wrapAll($results);
    }

    private function setAddedUpdatedTimestamps(Artisan $entity): void
    {
        if (null === $entity->getId()) {
            $entity->setDateAdded(UtcClock::now());
        } else {
            $entity->setDateUpdated(UtcClock::now());
        }
    }

    private function updateContact(Artisan $originalInput, Artisan $fixedInput, Artisan $originalArtisan): void
    {
        $submittedContact = $originalInput->getContactInfoObfuscated();

        if (null !== $originalArtisan->getId() && $submittedContact === $originalArtisan->getContactInfoObfuscated()) {
            $originalInput->updateContact($originalArtisan->getContactInfoOriginal());
            $fixedInput->updateContact($originalArtisan->getContactInfoOriginal());
        } else {
            $originalInput->updateContact($submittedContact);
            $fixedInput->updateContact($submittedContact);
        }
    }
}
