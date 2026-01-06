<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\Data\Definitions\ContactPermit;
use App\Data\Fixer\Fixer;
use App\Entity\Event;
use App\Entity\Submission;
use App\IuHandling\Import\Update;
use App\IuHandling\Import\UpdatesService;
use App\IuHandling\SubmissionService;
use App\Repository\CreatorRepository;
use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use stdClass;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[Small]
class UpdatesServiceTest extends FuzzrakeTestCase
{
    use ClockSensitiveTrait;

    #[AllowMockObjectsWithoutExpectations]
    public function testUpdateHandlesNewEmailProperly(): void
    {
        $submission = SubmissionService::getEntityForSubmission(new Creator()
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('getfursu.it@localhost.localdomain')
        );

        $subject = $this->getUpdatesServiceForGetUpdateFor([[['TEST001'], []]]);
        $result = $subject->getUpdateFor($submission);

        self::assertSame('', $result->originalCreator->getEmailAddress());

        self::assertSame('getfursu.it@localhost.localdomain', $result->updatedCreator->getEmailAddress());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testUpdateHandlesEmailChangeProperly(): void
    {
        $existing = $this->getPersistedCreatorMock()
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('getfursu.it@localhost.localdomain')
        ;

        $submission = SubmissionService::getEntityForSubmission(new Creator()
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('an-update.2@localhost.localdomain')
        );

        $subject = $this->getUpdatesServiceForGetUpdateFor([[['TEST001'], [$existing]]]);
        $result = $subject->getUpdateFor($submission);

        self::assertSame('getfursu.it@localhost.localdomain', $result->originalCreator->getEmailAddress());
        self::assertSame('an-update.2@localhost.localdomain', $result->updatedCreator->getEmailAddress());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testUpdateHandlesUnchangedEmailProperly(): void
    {
        $creator = $this->getPersistedCreatorMock()
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('getfursu.it@localhost.localdomain')
        ;

        $submission = SubmissionService::getEntityForSubmission(new Creator()
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('')
        );

        $subject = $this->getUpdatesServiceForGetUpdateFor([[['TEST001'], [$creator]]]);
        $result = $subject->getUpdateFor($submission);

        self::assertSame('getfursu.it@localhost.localdomain', $result->originalCreator->getEmailAddress());
        self::assertSame('getfursu.it@localhost.localdomain', $result->updatedCreator->getEmailAddress());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testUpdateHandlesRevokedContactPermitProperly(): void
    {
        $existing = $this->getPersistedCreatorMock()
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('getfursu.it@localhost.localdomain')
        ;

        $submission = SubmissionService::getEntityForSubmission(new Creator()
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('an-update.2@localhost.localdomain') // Should be ignored
            ->setContactAllowed(ContactPermit::NO)
        );

        $subject = $this->getUpdatesServiceForGetUpdateFor([[['TEST001'], [$existing]]]);
        $result = $subject->getUpdateFor($submission);

        self::assertSame('getfursu.it@localhost.localdomain', $result->originalCreator->getEmailAddress());
        self::assertSame('', $result->updatedCreator->getEmailAddress());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testAddedDateIsHandledProperly(): void
    {
        self::mockTime();

        $submission = SubmissionService::getEntityForSubmission(new Creator()
            ->setCreatorId('TEST001')
        );

        $subject = $this->getUpdatesServiceForGetUpdateFor([[['TEST001'], []]]);
        $result = $subject->getUpdateFor($submission);

        self::assertNull($result->originalCreator->getDateAdded());
        self::assertNull($result->originalCreator->getDateUpdated());

        self::assertDateTimeSameIgnoreSubSeconds(UtcClock::now(), $result->originalInput->getDateAdded());
        self::assertNull($result->originalInput->getDateUpdated());

        self::assertDateTimeSameIgnoreSubSeconds(UtcClock::now(), $result->updatedCreator->getDateAdded());
        self::assertNull($result->updatedCreator->getDateUpdated());
    }

    /**
     * @throws DateTimeException|JsonException|RandomException
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testUpdatedDateIsHandledProperly(): void
    {
        self::mockTime();

        $dateAdded = UtcClock::at('2022-09-09 09:09:09');

        $creator = $this->getPersistedCreatorMock()
            ->setCreatorId('TEST001')
            ->setDateAdded($dateAdded)
        ;

        $submission = SubmissionService::getEntityForSubmission(new Creator()->setCreatorId('TEST001'));

        $subject = $this->getUpdatesServiceForGetUpdateFor([[['TEST001'], [$creator]]]);
        $result = $subject->getUpdateFor($submission);

        self::assertDateTimeSameIgnoreSubSeconds($dateAdded, $result->originalCreator->getDateAdded());
        self::assertNull($result->originalCreator->getDateUpdated());

        self::assertDateTimeSameIgnoreSubSeconds($dateAdded, $result->originalInput->getDateAdded());
        self::assertDateTimeSameIgnoreSubSeconds(UtcClock::now(), $result->originalInput->getDateUpdated());

        self::assertDateTimeSameIgnoreSubSeconds($dateAdded, $result->updatedCreator->getDateAdded());
        self::assertDateTimeSameIgnoreSubSeconds(UtcClock::now(), $result->updatedCreator->getDateUpdated());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testResolvingMultipleMatchedByCreatorId(): void
    {
        // grep-code: At this point could only be a result of an error or unpredictable condition, but keeping this test

        $creator1 = $this->getPersistedCreatorMock()->setCreatorId('TEST0A1')->setName('Creator 1');
        $creator2 = $this->getPersistedCreatorMock()->setCreatorId('TEST0B1')->setName('Creator 2');

        $submission = SubmissionService::getEntityForSubmission(
            new Creator()
                ->setCreatorId('TEST0A1')
                ->setFormerCreatorIds(['TEST0B1'])
                ->setName('Creator X')
        );

        $subject = $this->getUpdatesServiceForGetUpdateFor([
            [['TEST0A1', 'TEST0B1'], [$creator1, $creator2]],
            [['TEST0A1'], [$creator1]],
        ]);

        $result = $subject->getUpdateFor($submission);
        self::assertEquals([$creator1, $creator2], $result->matchedCreators);

        $submission->setDirectives('match-maker-id TEST0A1');
        $result = $subject->getUpdateFor($submission);
        self::assertEquals([$creator1], $result->matchedCreators);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testUpdateHandlesCreatorIdChangeProperly(): void
    {
        $creator = $this->getPersistedCreatorMock()
            ->setCreatorId('TEST001')
            ->setFormerCreatorIds(['TEST002'])
            ->setName('The old creator name')
        ;

        // Changing
        $submission1 = SubmissionService::getEntityForSubmission(new Creator()
            ->setCreatorId('TEST003')
            ->setName('The new creator name')
            ->setFormerly(['The old creator name'])
        );

        $result1 = $this->getUpdatesServiceForGetUpdateFor([[['TEST003'], [$creator]]])->getUpdateFor($submission1);

        self::assertSame('The new creator name', $result1->updatedCreator->getName());
        self::assertEquals(['The old creator name'], $result1->updatedCreator->getFormerly());
        self::assertSame('TEST003', $result1->updatedCreator->getCreatorId());
        self::assertEquals(['TEST001', 'TEST002'], $result1->updatedCreator->getFormerCreatorIds());

        // No change
        $submission2 = SubmissionService::getEntityForSubmission(new Creator()
            ->setCreatorId('TEST001')
            ->setName('The new creator name')
            ->setFormerly(['The old creator name'])
        );

        $result2 = $this->getUpdatesServiceForGetUpdateFor([[['TEST001'], [$creator]]])->getUpdateFor($submission2);

        self::assertSame('The new creator name', $result2->updatedCreator->getName());
        self::assertEquals(['The old creator name'], $result2->updatedCreator->getFormerly());
        self::assertSame('TEST001', $result2->updatedCreator->getCreatorId());
        self::assertEquals(['TEST002'], $result2->updatedCreator->getFormerCreatorIds());
    }

    /**
     * @param list<array{list<string>, list<Creator>}> $calls
     */
    private function getUpdatesServiceForGetUpdateFor(array $calls): UpdatesService
    {
        $creatorRepoMock = $this->createMock(CreatorRepository::class);
        $creatorRepoMock->method('findByCreatorIds')->willReturnCallback(function (array $creatorIds) use ($calls) {
            foreach ($calls as $call) {
                if ($call[0] === $creatorIds) {
                    return arr_map($call[1], static fn (Creator $creator) => $creator->entity);
                }
            }

            self::fail('findByCreatorIds was called with unexpected parameters');
        });

        $entityManagerStub = self::createStub(EntityManagerInterface::class);
        $messageBusStub = self::createStub(MessageBusInterface::class);
        $loggerStub = self::createStub(LoggerInterface::class);

        return new UpdatesService($creatorRepoMock, $entityManagerStub, $this->getNoopFixerMock(), $messageBusStub, $loggerStub);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testAdditionCreatesCorrespondingEvent(): void
    {
        $entity = new Creator()->setCreatorId('TEST0001');
        $update = new Update(new Submission(), [], $entity, $entity, $entity, [], true, true);

        $subject = $this->getUpdatesServiceForImport($update);
        $subject->import($update);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testUpdateCreatesCorrespondingEvent(): void
    {
        $entity = new Creator()->setCreatorId('TEST0001');
        $update = new Update(new Submission(), [], $entity, $entity, $entity, [], true, false);

        $subject = $this->getUpdatesServiceForImport($update);
        $subject->import($update);
    }

    private function getUpdatesServiceForImport(Update $update): UpdatesService
    {
        $creatorPersisted = false;
        $eventPersisted = false;

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::exactly(2))->method('persist')->willReturnCallback(
            function (object $entity) use ($update, &$creatorPersisted, &$eventPersisted): void {
                if ($entity instanceof Creator) {
                    self::assertFalse($creatorPersisted, 'Expected single creator to be persisted.');
                    self::assertSame($update->originalCreator, $entity);
                    $creatorPersisted = true;
                } elseif ($entity instanceof Event) {
                    self::assertFalse($eventPersisted, 'Expected single event to be persisted.');
                    self::assertSame($update->originalCreator->getCreatorId(), $entity->getCreatorId());
                    self::assertSame($update->isNew ? Event::TYPE_CREATOR_ADDED : Event::TYPE_CREATOR_UPDATED, $entity->getType());
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
        $loggerStub = self::createStub(LoggerInterface::class);

        return new UpdatesService($creatorRepoStub, $entityManagerMock, $this->getNoopFixerMock(), $messageBusMock, $loggerStub);
    }

    private function getNoopFixerMock(): Fixer&MockObject
    {
        $fixerMock = $this->createMock(Fixer::class);
        $fixerMock->method('getFixed')->willReturnCallback(static fn (object $input) => clone $input);

        return $fixerMock;
    }
}
