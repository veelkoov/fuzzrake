<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\IuHandling\Import;

use App\Data\Fixer\Fixer;
use App\Data\LabelType;
use App\Entity\Event;
use App\Entity\Submission;
use App\Entity\User;
use App\IuHandling\Import\ImportData;
use App\IuHandling\Import\ImportService;
use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tests\TestUtils\UserCreator;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Small;
use stdClass;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[Small]
class ImportServiceTest extends FuzzrakeTestCase
{
    use ClockSensitiveTrait;

    public function testAddedDateIsHandledProperly(): void
    {
        self::mockTime();

        $submissionData = new Creator()->setCreatorId('TEST001');
        $submission = $this->getEntityForSubmission(new User(), $submissionData, false);

        $subject = $this->getImportServiceMockedForGetImportDataFor();
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

        $subject = $this->getImportServiceMockedForGetImportDataFor();
        $result = $subject->getImportDataFor($submission);

        self::assertDateTimeSameIgnoreSubSeconds($dateAdded, $result->subjectCreator->getDateAdded());
        self::assertNull($result->subjectCreator->getDateUpdated());

        self::assertDateTimeSameIgnoreSubSeconds($dateAdded, $result->inputData->getDateAdded());
        self::assertDateTimeSameIgnoreSubSeconds(UtcClock::now(), $result->inputData->getDateUpdated());

        self::assertDateTimeSameIgnoreSubSeconds($dateAdded, $result->fixedData->getDateAdded());
        self::assertDateTimeSameIgnoreSubSeconds(UtcClock::now(), $result->fixedData->getDateUpdated());
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

        $result1 = $this->getImportServiceMockedForGetImportDataFor()->getImportDataFor($submission1);

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

        $result2 = $this->getImportServiceMockedForGetImportDataFor()->getImportDataFor($submission2);

        self::assertSame('The new creator name', $result2->fixedData->getName());
        self::assertEquals(['The old creator name'], $result2->fixedData->getFormerly());
        self::assertSame('TEST001', $result2->fixedData->getCreatorId());
        self::assertEquals(['TEST002'], $result2->fixedData->getFormerCreatorIds());
    }

    private function getImportServiceMockedForGetImportDataFor(): ImportService
    {
        $entityManagerStub = self::createStub(EntityManagerInterface::class);
        $messageBusStub = self::createStub(MessageBusInterface::class);

        return new ImportService($entityManagerStub, $this->getNoopFixerMock(), $messageBusStub);
    }

    public function testAdditionCreatesCorrespondingEvent(): void
    {
        $creator = UserCreator::get()->setCreatorId('TEST0001');
        $submission = new Submission($creator->entity->getUser(), false);
        $importData = new ImportData($submission, $creator, $creator, $creator, [], true);

        $subject = $this->getImportServiceMockedForImport($importData);
        $subject->import($importData);
    }

    public function testUpdateCreatesCorrespondingEvent(): void
    {
        $creator = UserCreator::get()->setCreatorId('TEST0001');
        $submission = new Submission($creator->entity->getUser(), true);
        $importData = new ImportData($submission, $creator, $creator, $creator, [], true);

        $subject = $this->getImportServiceMockedForImport($importData);
        $subject->import($importData);
    }

    public function testAddedReceivesPendingReviewLabel(): void
    {
        $creator = UserCreator::get()->setCreatorId('TEST0001');
        $submission = new Submission($creator->entity->getUser(), false);
        $importData = new ImportData($submission, $creator, $creator, $creator, [], true);

        $subject = $this->getImportServiceMockedForImport($importData);
        $subject->import($importData);

        $label = $creator->findLabel(LabelType::CREATOR_PENDING_REVIEW, '');
        self::assertNotNull($label);
        self::assertTrue($label->active);
    }

    public function testUpdatedDoesNotReceivePendingReviewLabel(): void
    {
        $creator = UserCreator::get()->setCreatorId('TEST0001');
        $submission = new Submission($creator->entity->getUser(), true);
        $importData = new ImportData($submission, $creator, $creator, $creator, [], true);

        $subject = $this->getImportServiceMockedForImport($importData);
        $subject->import($importData);

        $label = $creator->findLabel(LabelType::CREATOR_PENDING_REVIEW, '');
        self::assertNull($label);
    }

    private function getImportServiceMockedForImport(ImportData $importData): ImportService
    {
        $creatorPersisted = false;
        $eventPersisted = false;

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::exactly(2))->method('persist')->willReturnCallback(
            function (object $entity) use ($importData, &$creatorPersisted, &$eventPersisted): void {
                if ($entity instanceof Creator) {
                    self::assertFalse($creatorPersisted, 'Expected single creator to be persisted.');
                    self::assertSame($importData->subjectCreator, $entity);
                    $creatorPersisted = true;
                } elseif ($entity instanceof Event) {
                    self::assertFalse($eventPersisted, 'Expected single event to be persisted.');
                    self::assertSame($importData->subjectCreator->getCreatorId(), $entity->getCreatorId());
                    self::assertSame(!$importData->isUpdate() ? Event::TYPE_CREATOR_ADDED : Event::TYPE_CREATOR_UPDATED, $entity->getType());
                    $eventPersisted = true;
                } else {
                    self::fail('Unexpected entity type is being persisted.');
                }
            });
        $entityManagerMock->expects(self::once())->method('flush');

        $messageBusMock = $this->createMock(MessageBusInterface::class);
        $messageBusMock->expects(self::exactly(2)) // Species sync and cache invalidation
            ->method('dispatch')->willReturn(new Envelope(new stdClass()));

        return new ImportService($entityManagerMock, $this->getNoopFixerMock(), $messageBusMock);
    }

    private function getNoopFixerMock(): Fixer
    {
        $fixerMock = self::createStub(Fixer::class);
        $fixerMock->method('getFixed')->willReturnCallback(static fn (Creator $input) => $input->copy());

        return $fixerMock;
    }
}
