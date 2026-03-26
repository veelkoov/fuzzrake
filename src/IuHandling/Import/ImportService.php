<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\Fields\Fields;
use App\Data\Definitions\Fields\FieldsList;
use App\Data\Fixer\Fixer;
use App\Entity\Event;
use App\Entity\Submission;
use App\IuHandling\Exception\ManagerConfigError;
use App\Repository\CreatorRepository;
use App\Utils\Collections\Arrays;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\FieldReadInterface;
use App\Utils\UnbelievableRuntimeException;
use App\ValueObject\CacheTags;
use App\ValueObject\Messages\InvalidateCacheTagsV1;
use App\ValueObject\Messages\SpeciesSyncNotificationV1;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ImportService
{
    public function __construct(
        private readonly CreatorRepository $creatorRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Fixer $fixer,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function getImportDataFor(Submission $submission): ImportData
    {
        [$directivesError, $manager] = $this->getManager($submission);
        $errors = Arrays::nonEmptyStrings([$directivesError]);

        $inputData = new Creator();
        $this->updateWith($inputData, $submission->getReader(), Fields::readFromSubmissionData());

        $fixedInput = $this->fixer->getFixed($inputData);

        // grep-code-legacy-submissions-with-no-creator-reference
        $matchedCreators = $this->getCreators($fixedInput, $manager->getMatchedCreatorId()); // FIXME: Remove or simplify

        if (1 === count($matchedCreators)) {
            $subjectCreator = Arrays::single($matchedCreators);
        } else {
            // grep-code-legacy-submissions-with-no-creator-reference
            $subjectCreator = new Creator(user: $submission->getOwner()); // TODO: Temporary support for legacy submissions

            if ([] !== $matchedCreators) {
                $errors[] = 'Single creator must get selected.';
            }
        }

        $this->handleSpecialFieldsInInput($inputData, $subjectCreator);
        $this->handleSpecialFieldsInInput($fixedInput, $subjectCreator);

        $fixedData = $subjectCreator->copy();
        $this->updateWith($fixedData, $fixedInput, Fields::iuFormAffected());
        $manager->correctCreator($fixedData);

        return new ImportData(
            $submission,
            $matchedCreators,
            $subjectCreator,
            $inputData,
            $fixedData,
            $errors,
            $manager->isAccepted(),
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

    private function updateWith(Creator $creator, FieldReadInterface $source, FieldsList $fields): void
    {
        foreach ($fields as $field) {
            $creator->set($field, $source->get($field));
        }

        $creator->assureNsfwSafety();
    }

    /**
     * @return Creator[]
     */
    private function getCreators(Creator $submissionData, ?string $matchedCreatorId): array
    {
        return Creator::wrapAll($this->creatorRepository->findByCreatorIds(
            null !== $matchedCreatorId
                ? [$matchedCreatorId]
                : $submissionData->getAllCreatorIds(),
        ));
    }

    private function handleSpecialFieldsInInput(Creator $input, Creator $original): void
    {
        if (null === $original->getId()) {
            $input->setDateAdded(UtcClock::now());
        } else {
            $input->setDateAdded($original->getDateAdded());
            $input->setDateUpdated(UtcClock::now());

            if ($input->getCreatorId() !== $original->getCreatorId()) {
                $input->setFormerCreatorIds($original->getAllCreatorIds());
            } else {
                $input->setFormerCreatorIds($original->getFormerCreatorIds());
            }
        }
    }

    public function import(ImportData $importData): void
    {
        $existingEntity = $importData->subjectCreator;
        $cloneWithUpdates = $importData->fixedData;

        foreach (Fields::persisted() as $field) {
            $existingEntity->set($field, $cloneWithUpdates->get($field));
        }

        $this->entityManager->persist($existingEntity);
        $this->entityManager->persist($this->getEventFor($importData));
        $this->entityManager->flush();

        try {
            $this->messageBus->dispatch(new SpeciesSyncNotificationV1());
            $this->messageBus->dispatch(new InvalidateCacheTagsV1(CacheTags::CREATORS));
        } catch (ExceptionInterface $exception) {
            throw new RuntimeException(previous: $exception);
        }
    }

    private function getEventFor(ImportData $importData): Event
    {
        return new Event()
            ->setType($importData->submission->getIsUpdate() ? Event::TYPE_CREATOR_UPDATED : Event::TYPE_CREATOR_ADDED)
            ->setCreatorId($importData->subjectCreator->getCreatorId());
    }
}
