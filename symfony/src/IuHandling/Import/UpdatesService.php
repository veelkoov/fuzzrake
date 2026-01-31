<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\ContactPermit;
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
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdatesService
{
    public function __construct(
        private readonly CreatorRepository $creatorRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Fixer $fixer,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger, // @phpstan-ignore property.onlyWritten (Leave for future uses)
    ) {
    }

    public function getUpdateFor(Submission $submission): Update
    {
        [$directivesError, $manager] = $this->getManager($submission);
        $errors = Arrays::nonEmptyStrings([$directivesError]);

        $originalInput = new Creator();
        $this->updateWith($originalInput, $submission->getReader(), Fields::readFromSubmissionData());

        $fixedInput = $this->fixer->getFixed($originalInput);

        $matchedCreators = $this->getCreators($fixedInput, $manager->getMatchedCreatorId());

        if (1 === count($matchedCreators)) {
            $originalCreator = Arrays::single($matchedCreators);
        } else {
            $originalCreator = new Creator();

            if ([] !== $matchedCreators) {
                $errors[] = 'Single creator must get selected.';
            }
        }

        $this->handleSpecialFieldsInInput($originalInput, $originalCreator);
        $this->handleSpecialFieldsInInput($fixedInput, $originalCreator);

        $updatedCreator = clone $originalCreator;
        $this->updateWith($updatedCreator, $fixedInput, Fields::iuFormAffected());

        $manager->correctCreator($updatedCreator);

        $isNew = null === $originalCreator->getId();
        $isAccepted = $manager->isAccepted();

        if (!$isNew && $originalCreator->getPassword() !== $updatedCreator->getPassword() && !$isAccepted) {
            $errors[] = 'Password does not match.';
        }

        return new Update(
            $submission,
            $matchedCreators,
            $originalInput,
            $originalCreator,
            $updatedCreator,
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

    private function handleSpecialFieldsInInput(Creator $submission, Creator $original): void
    {
        if (ContactPermit::NO === $submission->getContactAllowed()) {
            $submission->setEmailAddress('');
        } elseif ('' === $submission->getEmailAddress()) {
            $submission->setEmailAddress($original->getEmailAddress());
        }

        if (null === $original->getId()) {
            $submission->setDateAdded(UtcClock::now());
        } else {
            $submission->setDateAdded($original->getDateAdded());
            $submission->setDateUpdated(UtcClock::now());

            if ($submission->getCreatorId() !== $original->getCreatorId()) {
                $submission->setFormerCreatorIds($original->getAllCreatorIds());
            } else {
                $submission->setFormerCreatorIds($original->getFormerCreatorIds());
            }
        }
    }

    public function import(Update $update): void
    {
        $existingEntity = $update->originalCreator;
        $cloneWithUpdates = $update->updatedCreator;

        foreach (Fields::persisted() as $field) {
            $existingEntity->set($field, $cloneWithUpdates->get($field));
        }

        $this->entityManager->persist($existingEntity);
        $this->entityManager->persist($this->getEventFor($update));
        $this->entityManager->flush();

        try {
            $this->messageBus->dispatch(new SpeciesSyncNotificationV1());
            $this->messageBus->dispatch(new InvalidateCacheTagsV1(CacheTags::CREATORS));
        } catch (ExceptionInterface $exception) {
            throw new RuntimeException(previous: $exception);
        }
    }

    private function getEventFor(Update $update): Event
    {
        return new Event()
            ->setType($update->isNew ? Event::TYPE_CREATOR_ADDED : Event::TYPE_CREATOR_UPDATED)
            ->setCreatorId($update->originalCreator->getCreatorId());
    }
}
