<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\Data\Definitions\ContactPermit;
use App\Data\Fixer\Fixer;
use App\IuHandling\Exception\SubmissionException;
use App\IuHandling\Import\UpdatesService;
use App\IuHandling\Submission\SubmissionService;
use App\Repository\CreatorRepository;
use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\TestUtils\UtcClockMock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use Psl\Vec;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[Small]
class UpdatesServiceTest extends FuzzrakeTestCase
{
    public function testUpdateHandlesNewEmailProperly(): void
    {
        $submission = SubmissionService::getEntityForSubmission((new Creator())
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('getfursu.it@localhost.localdomain')
        );

        $subject = $this->getSetUpUpdatesService([[['A creator'], ['TEST001'], []]]);
        $result = $subject->getUpdateFor($submission);

        self::assertSame('', $result->originalCreator->getEmailAddress());

        self::assertSame('getfursu.it@localhost.localdomain', $result->updatedCreator->getEmailAddress());
    }

    public function testUpdateHandlesEmailChangeProperly(): void
    {
        $existing = $this->getPersistedCreatorMock()
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('getfursu.it@localhost.localdomain')
        ;

        $submission = SubmissionService::getEntityForSubmission((new Creator())
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('an-update.2@localhost.localdomain')
        );

        $subject = $this->getSetUpUpdatesService([[['A creator'], ['TEST001'], [$existing]]]);
        $result = $subject->getUpdateFor($submission);

        self::assertSame('getfursu.it@localhost.localdomain', $result->originalCreator->getEmailAddress());
        self::assertSame('an-update.2@localhost.localdomain', $result->updatedCreator->getEmailAddress());
    }

    public function testUpdateHandlesUnchangedEmailProperly(): void
    {
        $creator = $this->getPersistedCreatorMock()
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('getfursu.it@localhost.localdomain')
        ;

        $submission = SubmissionService::getEntityForSubmission((new Creator())
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('')
        );

        $subject = $this->getSetUpUpdatesService([[['A creator'], ['TEST001'], [$creator]]]);
        $result = $subject->getUpdateFor($submission);

        self::assertSame('getfursu.it@localhost.localdomain', $result->originalCreator->getEmailAddress());
        self::assertSame('getfursu.it@localhost.localdomain', $result->updatedCreator->getEmailAddress());
    }

    public function testUpdateHandlesRevokedContactPermitProperly(): void
    {
        $existing = $this->getPersistedCreatorMock()
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('getfursu.it@localhost.localdomain')
        ;

        $submission = SubmissionService::getEntityForSubmission((new Creator())
            ->setName('A creator')
            ->setCreatorId('TEST001')
            ->setEmailAddress('an-update.2@localhost.localdomain') // Should be ignored
            ->setContactAllowed(ContactPermit::NO)
        );

        $subject = $this->getSetUpUpdatesService([[['A creator'], ['TEST001'], [$existing]]]);
        $result = $subject->getUpdateFor($submission);

        self::assertSame('getfursu.it@localhost.localdomain', $result->originalCreator->getEmailAddress());
        self::assertSame('', $result->updatedCreator->getEmailAddress());
    }

    public function testAddedDateIsHandledProperly(): void
    {
        UtcClockMock::start();

        $submission = SubmissionService::getEntityForSubmission((new Creator())
            ->setCreatorId('TEST001')
        );

        $subject = $this->getSetUpUpdatesService([
            [[''], ['TEST001'], []],
        ]);
        $result = $subject->getUpdateFor($submission);

        self::assertEquals(null, $result->originalCreator->getDateAdded());
        self::assertEquals(null, $result->originalCreator->getDateUpdated());

        self::assertEquals(UtcClock::now(), $result->originalInput->getDateAdded());
        self::assertEquals(null, $result->originalInput->getDateUpdated());

        self::assertEquals(UtcClock::now(), $result->updatedCreator->getDateAdded());
        self::assertEquals(null, $result->updatedCreator->getDateUpdated());
    }

    /**
     * @throws SubmissionException|DateTimeException
     */
    public function testUpdatedDateIsHandledProperly(): void
    {
        UtcClockMock::start();

        $dateAdded = UtcClock::at('2022-09-09 09:09:09');

        $creator = $this->getPersistedCreatorMock()
            ->setCreatorId('TEST001')
            ->setDateAdded($dateAdded)
        ;

        $submission = SubmissionService::getEntityForSubmission((new Creator())
            ->setCreatorId('TEST001')
        );

        $subject = $this->getSetUpUpdatesService([
            [[''], ['TEST001'], [$creator]],
        ]);
        $result = $subject->getUpdateFor($submission);

        self::assertEquals($dateAdded, $result->originalCreator->getDateAdded());
        self::assertEquals(null, $result->originalCreator->getDateUpdated());

        self::assertEquals($dateAdded, $result->originalInput->getDateAdded());
        self::assertEquals(UtcClock::now(), $result->originalInput->getDateUpdated());

        self::assertEquals($dateAdded, $result->updatedCreator->getDateAdded());
        self::assertEquals(UtcClock::now(), $result->updatedCreator->getDateUpdated());
    }

    /**
     * @param list<string> $initialUrlPhotos
     * @param list<string> $initialMiniatures
     * @param list<string> $newUrlPhotos
     * @param list<string> $expectedMiniatures
     */
    #[DataProvider('imagesUpdateShouldResetMiniaturesDataProvider')]
    public function testUpdateHandlesImagesUpdateProperly(array $initialUrlPhotos, array $initialMiniatures, array $newUrlPhotos, array $expectedMiniatures): void
    {
        $creator = $this->getPersistedCreatorMock()
            ->setCreatorId('TEST001')
            ->setPhotoUrls($initialUrlPhotos)
            ->setMiniatureUrls($initialMiniatures)
        ;

        $submission = SubmissionService::getEntityForSubmission((new Creator())
            ->setCreatorId('TEST001')
            ->setPhotoUrls($newUrlPhotos)
        );

        $subject = $this->getSetUpUpdatesService([
            [[''], ['TEST001'], [$creator]],
        ]);
        $result = $subject->getUpdateFor($submission);

        self::assertEquals($expectedMiniatures, $result->updatedCreator->getMiniatureUrls());
    }

    /**
     * @return array<string, array{list<string>, list<string>, list<string>, list<string>}>
     */
    public static function imagesUpdateShouldResetMiniaturesDataProvider(): array
    {
        return [
            'No photos at all'         => [[], [], [], []],
            'No photos before, adding' => [[], [], ['NEW_PHOTOS'], []],
            'Clearing existing photos' => [['OLD_PHOTOS'], ['OLD_MINIATURES'], [], []],
            'Changing photos'          => [['OLD_PHOTOS'], ['OLD_MINIATURES'], ['NEW_PHOTOS'], []],
            'Photos exist, unchanged'  => [['OLD_PHOTOS'], ['OLD_MINIATURES'], ['OLD_PHOTOS'], ['OLD_MINIATURES']],
        ];
    }

    public function testResolvingMultipleMatchedByCreatorId(): void
    {
        $creator1 = $this->getPersistedCreatorMock()
            ->setCreatorId('TEST0A1')
            ->setName('Common name')
        ;

        $creator2 = $this->getPersistedCreatorMock()
            ->setCreatorId('TEST0B1')
            ->setName('Common part')
        ;

        $submission = SubmissionService::getEntityForSubmission((new Creator())
            ->setCreatorId('TEST0A2')
            ->setName('Common')
        );

        $subject = $this->getSetUpUpdatesService([
            [['Common'], ['TEST0A2'], [$creator1, $creator2]],
            [[], ['TEST0A1'], [$creator1]],
        ]);

        $result = $subject->getUpdateFor($submission);
        self::assertEquals([$creator1, $creator2], $result->matchedCreators);

        $submission->setDirectives('match-maker-id TEST0A1');
        $result = $subject->getUpdateFor($submission);
        self::assertEquals([$creator1], $result->matchedCreators);
    }

    public function testUpdateHandlesCreatorIdChangeProperly(): void
    {
        $creator = $this->getPersistedCreatorMock()
            ->setCreatorId('TEST001')
            ->setFormerCreatorIds(['TEST002'])
            ->setName('The old creator name')
        ;

        // Changing
        $submission1 = SubmissionService::getEntityForSubmission(Creator::new()
            ->setCreatorId('TEST003')
            ->setName('The new creator name')
            ->setFormerly(['The old creator name'])
        );

        $result1 = $this->getSetUpUpdatesService([
            [['The new creator name', 'The old creator name'], ['TEST003'], [$creator]],
        ])->getUpdateFor($submission1);

        self::assertSame('The new creator name', $result1->updatedCreator->getName());
        self::assertEquals(['The old creator name'], $result1->updatedCreator->getFormerly());
        self::assertSame('TEST003', $result1->updatedCreator->getCreatorId());
        self::assertEquals(['TEST001', 'TEST002'], $result1->updatedCreator->getFormerCreatorIds());

        // No change
        $submission2 = SubmissionService::getEntityForSubmission(Creator::new()
            ->setCreatorId('TEST001')
            ->setName('The new creator name')
            ->setFormerly(['The old creator name'])
        );

        $result2 = $this->getSetUpUpdatesService([
            [['The new creator name', 'The old creator name'], ['TEST001'], [$creator]],
        ])->getUpdateFor($submission2);

        self::assertSame('The new creator name', $result2->updatedCreator->getName());
        self::assertEquals(['The old creator name'], $result2->updatedCreator->getFormerly());
        self::assertSame('TEST001', $result2->updatedCreator->getCreatorId());
        self::assertEquals(['TEST002'], $result2->updatedCreator->getFormerCreatorIds());
    }

    /**
     * @param list<array{list<string>, list<string>, list<Creator>}> $calls
     */
    private function getSetUpUpdatesService(array $calls): UpdatesService
    {
        $creatorRepoMock = $this->createMock(CreatorRepository::class);
        $creatorRepoMock->method('findBestMatches')->willReturnCallback(function (array $names, array $creatorIds) use ($calls) {
            foreach ($calls as $call) {
                if ($call[0] === $names && $call[1] === $creatorIds) {
                    return Vec\map($call[2], static fn (Creator $creator) => $creator->getCreator());
                }
            }

            self::fail('findBestMatches was called with unexpected parameters');
        });

        $fixerMock = $this->createMock(Fixer::class);
        $fixerMock->method('getFixed')->willReturnCallback(fn (object $input) => clone $input);

        $messageBusStub = self::createStub(MessageBusInterface::class);
        $loggerStub = self::createStub(LoggerInterface::class);

        return new UpdatesService($creatorRepoMock, $fixerMock, $messageBusStub, $loggerStub);
    }
}
