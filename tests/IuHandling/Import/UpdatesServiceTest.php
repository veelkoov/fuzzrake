<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\Data\Fixer\Fixer;
use App\Entity\Event;
use App\Entity\Submission;
use App\Entity\User;
use App\IuHandling\Import\ImportData;
use App\IuHandling\Import\ImportService;
use App\Repository\CreatorRepository;
use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tests\TestUtils\UserCreator;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[Small]
class UpdatesServiceTest extends FuzzrakeTestCase
{
    use ClockSensitiveTrait;

    public function testAddedDateIsHandledProperly(): void
    {
        self::mockTime();

        $submissionData = new Creator()->setCreatorId('TEST001');
        $submission = $this->getEntityForSubmission(new User(), $submissionData, false);

        $subject = $this->getUpdatesServiceForGetUpdateFor([[['TEST001'], []]]);
        $result = $subject->getImportDataFor($submission);

        self::assertNull($result->subjectCreator->getDateAdded());
        self::assertNull($result->subjectCreator->getDateUpdated());

        self::assertDateTimeSameIgnoreSubSeconds(UtcClock::now(), $result->inputData->getDateAdded());
        self::assertNull($result->inputData->getDateUpdated());

        self::assertDateTimeSameIgnoreSubSeconds(UtcClock::now(), $result->fixedData->getDateAdded());
        self::assertNull($result->fixedData->getDateUpdated());
    }

    /**
     * @throws DateTimeException
     */
    public function testUpdatedDateIsHandledProperly(): void
    {
        self::mockTime();

        $dateAdded = UtcClock::at('2022-09-09 09:09:09');

        $creator = $this->getPersistedCreatorMock()
            ->setCreatorId('TEST001')
            ->setDateAdded($dateAdded)
        ;
        $user = new User()->setCreator($creator->entity);

        $submissionData = new Creator()->setCreatorId('TEST001');
        $submission = $this->getEntityForSubmission($user, $submissionData, true);

        $subject = $this->getUpdatesServiceForGetUpdateFor([[['TEST001'], [$creator]]]);
        $result = $subject->getImportDataFor($submission);

        self::assertDateTimeSameIgnoreSubSeconds($dateAdded, $result->subjectCreator->getDateAdded());
        self::assertNull($result->subjectCreator->getDateUpdated());

        self::assertDateTimeSameIgnoreSubSeconds($dateAdded, $result->inputData->getDateAdded());
        self::assertDateTimeSameIgnoreSubSeconds(UtcClock::now(), $result->inputData->getDateUpdated());

        self::assertDateTimeSameIgnoreSubSeconds($dateAdded, $result->fixedData->getDateAdded());
        self::assertDateTimeSameIgnoreSubSeconds(UtcClock::now(), $result->fixedData->getDateUpdated());
    }

    public function testResolvingMultipleMatchedByCreatorId(): void
    {
        // grep-code: At this point could only be a result of an error or unpredictable condition, but keeping this test

        $creator1 = $this->getPersistedCreatorMock()->setCreatorId('TEST0A1')->setName('Creator 1');
        $creator2 = $this->getPersistedCreatorMock()->setCreatorId('TEST0B1')->setName('Creator 2');

        $submissionData = new Creator()
            ->setCreatorId('TEST0A1')
            ->setFormerCreatorIds(['TEST0B1'])
            ->setName('Creator X')
        ;
        $submission = $this->getEntityForSubmission(new User(), $submissionData, true); // FIXME: Should match by data in User; test needs redesign/rethink

        $subject = $this->getUpdatesServiceForGetUpdateFor([
            [['TEST0A1', 'TEST0B1'], [$creator1, $creator2]],
            [['TEST0A1'], [$creator1]],
        ]);

        $result = $subject->getImportDataFor($submission);
        self::assertEquals([$creator1, $creator2], $result->matchedCreators);

        $submission->setDirectives('match-maker-id TEST0A1');
        $result = $subject->getImportDataFor($submission);
        self::assertEquals([$creator1], $result->matchedCreators);
    }

    public function testUpdateHandlesCreatorIdChangeProperly(): void
    {
        $creator = $this->getPersistedCreatorMock()
            ->setCreatorId('TEST001')
            ->setFormerCreatorIds(['TEST002'])
            ->setName('The old creator name')
        ;
        $user = new User()->setCreator($creator->entity);

        // Changing
        $submissionData1 = new Creator()
            ->setCreatorId('TEST003')
            ->setName('The new creator name')
            ->setFormerly(['The old creator name'])
        ;
        $submission1 = $this->getEntityForSubmission($user, $submissionData1, true);

        $result1 = $this->getUpdatesServiceForGetUpdateFor([[['TEST003'], [$creator]]])->getImportDataFor($submission1);

        self::assertSame('The new creator name', $result1->fixedData->getName());
        self::assertEquals(['The old creator name'], $result1->fixedData->getFormerly());
        self::assertSame('TEST003', $result1->fixedData->getCreatorId());
        self::assertEquals(['TEST001', 'TEST002'], $result1->fixedData->getFormerCreatorIds());

        // No change
        $submissionData2 = new Creator()
            ->setCreatorId('TEST001')
            ->setName('The new creator name')
            ->setFormerly(['The old creator name'])
        ;
        $submission2 = $this->getEntityForSubmission($user, $submissionData2, true);

        $result2 = $this->getUpdatesServiceForGetUpdateFor([[['TEST001'], [$creator]]])->getImportDataFor($submission2);

        self::assertSame('The new creator name', $result2->fixedData->getName());
        self::assertEquals(['The old creator name'], $result2->fixedData->getFormerly());
        self::assertSame('TEST001', $result2->fixedData->getCreatorId());
        self::assertEquals(['TEST002'], $result2->fixedData->getFormerCreatorIds());
    }

    /**
     * @param list<array{list<string>, list<Creator>}> $calls
     */
    private function getUpdatesServiceForGetUpdateFor(array $calls): ImportService
    {
        $creatorRepoMock = $this->createMock(CreatorRepository::class);
        $creatorRepoMock
            ->expects(self::atLeast(0))->method('findByCreatorIds')
            ->willReturnCallback(function (array $creatorIds) use ($calls) {
                foreach ($calls as $call) {
                    if ($call[0] === $creatorIds) {
                        return arr_map($call[1], static fn (Creator $creator) => $creator->entity);
                    }
                }

                self::fail('findByCreatorIds was called with unexpected parameters');
            });

        $entityManagerStub = self::createStub(EntityManagerInterface::class);
        $messageBusStub = self::createStub(MessageBusInterface::class);

        return new ImportService($creatorRepoMock, $entityManagerStub, $this->getNoopFixerMock(), $messageBusStub);
    }

    public function testAdditionCreatesCorrespondingEvent(): void
    {
        $entity = UserCreator::get()->setCreatorId('TEST0001');
        $submission = new Submission(false)->setOwner($entity->entity->getUser());
        $update = new ImportData($submission, [], $entity, $entity, $entity, [], true);

        $subject = $this->getUpdatesServiceForImport($update);
        $subject->import($update);
    }

    public function testUpdateCreatesCorrespondingEvent(): void
    {
        $entity = UserCreator::get()->setCreatorId('TEST0001');
        $submission = new Submission(true)->setOwner($entity->entity->getUser());
        $update = new ImportData($submission, [], $entity, $entity, $entity, [], true);

        $subject = $this->getUpdatesServiceForImport($update);
        $subject->import($update);
    }

    private function getUpdatesServiceForImport(ImportData $update): ImportService
    {
        $creatorPersisted = false;
        $eventPersisted = false;

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::exactly(2))->method('persist')->willReturnCallback(
            function (object $entity) use ($update, &$creatorPersisted, &$eventPersisted): void {
                if ($entity instanceof Creator) {
                    self::assertFalse($creatorPersisted, 'Expected single creator to be persisted.');
                    self::assertSame($update->subjectCreator, $entity);
                    $creatorPersisted = true;
                } elseif ($entity instanceof Event) {
                    self::assertFalse($eventPersisted, 'Expected single event to be persisted.');
                    self::assertSame($update->subjectCreator->getCreatorId(), $entity->getCreatorId());
                    self::assertSame(!$update->submission->getIsUpdate() ? Event::TYPE_CREATOR_ADDED : Event::TYPE_CREATOR_UPDATED, $entity->getType());
                    $eventPersisted = true;
                } else {
                    self::fail('Unexpected entity type is being persisted.');
                }
            });
        $entityManagerMock->expects(self::once())->method('flush');

        $messageBusMock = $this->createMock(MessageBusInterface::class);
        $messageBusMock->expects(self::exactly(2)) // Species sync and cache invalidation
            ->method('dispatch')->willReturn(new Envelope(new stdClass()));

        $creatorRepoStub = self::createStub(CreatorRepository::class);

        return new ImportService($creatorRepoStub, $entityManagerMock, $this->getNoopFixerMock(), $messageBusMock);
    }

    private function getNoopFixerMock(): Fixer&MockObject
    {
        $fixerMock = $this->createMock(Fixer::class);
        $fixerMock->expects(self::atLeast(0))->method('getFixed')
            ->willReturnCallback(static fn (Creator $input) => $input->copy());

        return $fixerMock;
    }
}
