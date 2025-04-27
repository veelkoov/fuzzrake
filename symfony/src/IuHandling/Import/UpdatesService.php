<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Fields;
use App\Data\Definitions\Fields\FieldsList;
use App\Data\Fixer\Fixer;
use App\Entity\Submission;
use App\IuHandling\Exception\ManagerConfigError;
use App\Repository\CreatorRepository;
use App\Utils\Collections\Arrays;
use App\Utils\Collections\StringLists;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\FieldReadInterface;
use App\Utils\UnbelievableRuntimeException;
use App\ValueObject\Messages\SpeciesSyncNotificationV1;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use function Psl\Vec\concat;
use function Psl\Vec\filter;

class UpdatesService
{
    public function __construct(
        private readonly CreatorRepository $creatorRepository,
        private readonly Fixer $fixer,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getUpdateFor(UpdateInput $input): Update
    {
        [$directivesError, $manager] = $this->getManager($input->submission);
        $errors = filter([$directivesError]);

        $originalInput = new Creator();
        $this->updateWith($originalInput, $input->submission, Fields::readFromSubmissionData());

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

        $this->handleSpecialFieldsInEntity($updatedCreator, $originalCreator);
        $manager->correctCreator($updatedCreator);

        $isNew = null === $originalCreator->getId();
        $isAccepted = $manager->isAccepted();

        if (!$isNew && $originalCreator->getPassword() !== $updatedCreator->getPassword() && !$isAccepted) {
            $errors[] = 'Password does not match.';
        }

        return new Update(
            $input->submission,
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
        if (null !== $matchedCreatorId) {
            $creatorIds = [$matchedCreatorId];
            $names = [];
        } else {
            $creatorIds = $submissionData->getAllCreatorIds();
            $names = concat([$submissionData->getName()], $submissionData->getFormerly());
        }

        $results = $this->creatorRepository->findBestMatches($names, $creatorIds);

        return Creator::wrapAll($results);
    }

    private function handleSpecialFieldsInInput(Creator $submission, Creator $original): void
    {
        if (ContactPermit::NO === $submission->getContactAllowed()) {
            $submission->setEmailAddress('');
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

    private function handleSpecialFieldsInEntity(Creator $updatedCreator, Creator $originalCreator): void
    {
        // Known limitation: unable to easily reorder photos grep-cannot-easily-reorder-photos
        if (!StringLists::sameElements($updatedCreator->getPhotoUrls(), $originalCreator->getPhotoUrls())) {
            $updatedCreator->setMiniatureUrls([]); // FIXME: https://github.com/veelkoov/fuzzrake/issues/160
        }
    }

    public function import(Update $update): void
    {
        $existingEntity = $update->originalCreator;
        $cloneWithUpdates = $update->updatedCreator;

        foreach (Fields::persisted() as $field) {
            $existingEntity->set($field, $cloneWithUpdates->get($field));
        }

        $this->creatorRepository->add($existingEntity->getCreator(), true);

        try {
            $this->messageBus->dispatch(new SpeciesSyncNotificationV1());
        } catch (ExceptionInterface $exception) {
            $this->logger->error("Failed dispatching species sync notification: {$exception->getMessage()}");
        }
    }
}
