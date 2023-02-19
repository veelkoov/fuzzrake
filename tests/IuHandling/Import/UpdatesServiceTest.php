<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\Data\Definitions\Fields\Field;
use App\Data\Fixer\Fixer;
use App\Entity\Submission;
use App\IuHandling\Exception\SubmissionException;
use App\IuHandling\Import\UpdateInput;
use App\IuHandling\Import\UpdatesService;
use App\Repository\ArtisanRepository;
use App\Tests\TestUtils\Cases\TestCase;
use App\Tests\TestUtils\Submissions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\TestUtils\UtcClockMock;

use function Psl\Vec\map;

/**
 * @small
 */
class UpdatesServiceTest extends TestCase
{
    public function testUpdateHandlesNewContactInfoProperly(): void
    {
        $submissionData = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
            ->setContactInfoObfuscated('getfursu.it@localhost.localdomain')
        );

        $subject = $this->getSetUpUpdatesService([
            [[''], ['MAKERID'], []],
        ]);
        $result = $subject->getUpdateFor(new UpdateInput($submissionData, new Submission()));

        self::assertEquals('', $result->originalArtisan->getContactInfoOriginal());
        self::assertEquals('', $result->originalArtisan->getContactMethod());
        self::assertEquals('', $result->originalArtisan->getContactAddressPlain());
        self::assertEquals('', $result->originalArtisan->getContactInfoObfuscated());

        self::assertEquals('getfursu.it@localhost.localdomain', $result->updatedArtisan->getContactInfoOriginal());
        self::assertEquals('E-MAIL', $result->updatedArtisan->getContactMethod());
        self::assertEquals('getfursu.it@localhost.localdomain', $result->updatedArtisan->getContactAddressPlain());
        self::assertEquals('E-MAIL: ge*******it@local***********omain', $result->updatedArtisan->getContactInfoObfuscated());
    }

    public function testUpdateHandlesContactInfoChangeProperly(): void
    {
        $artisan = $this->getPersistedArtisanMock()
            ->setMakerId('MAKERID')
            ->updateContact('getfursu.it@localhost.localdomain')
        ;

        $submissionData = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
            ->setContactInfoObfuscated('Telegram: @getfursuit')
        );

        $subject = $this->getSetUpUpdatesService([
            [[''], ['MAKERID'], [$artisan]],
        ]);
        $result = $subject->getUpdateFor(new UpdateInput($submissionData, new Submission()));

        self::assertEquals('getfursu.it@localhost.localdomain', $result->originalArtisan->getContactInfoOriginal());
        self::assertEquals('E-MAIL', $result->originalArtisan->getContactMethod());
        self::assertEquals('getfursu.it@localhost.localdomain', $result->originalArtisan->getContactAddressPlain());
        self::assertEquals('E-MAIL: ge*******it@local***********omain', $result->originalArtisan->getContactInfoObfuscated());

        self::assertEquals('Telegram: @getfursuit', $result->updatedArtisan->getContactInfoOriginal());
        self::assertEquals('TELEGRAM', $result->updatedArtisan->getContactMethod());
        self::assertEquals('@getfursuit', $result->updatedArtisan->getContactAddressPlain());
        self::assertEquals('TELEGRAM: @ge******it', $result->updatedArtisan->getContactInfoObfuscated());
    }

    public function testUpdateHandlesUnchangedContactInfoProperly(): void
    {
        $artisan = $this->getPersistedArtisanMock()
            ->setMakerId('MAKERID')
            ->updateContact('getfursu.it@localhost.localdomain')
        ;

        $submissionData = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
            ->setContactInfoObfuscated('E-MAIL: ge*******it@local***********omain')
        );

        $subject = $this->getSetUpUpdatesService([
            [[''], ['MAKERID'], [$artisan]],
        ]);
        $result = $subject->getUpdateFor(new UpdateInput($submissionData, new Submission()));

        self::assertEquals('getfursu.it@localhost.localdomain', $result->originalArtisan->getContactInfoOriginal());
        self::assertEquals('E-MAIL', $result->originalArtisan->getContactMethod());
        self::assertEquals('getfursu.it@localhost.localdomain', $result->originalArtisan->getContactAddressPlain());
        self::assertEquals('E-MAIL: ge*******it@local***********omain', $result->originalArtisan->getContactInfoObfuscated());

        self::assertEquals('getfursu.it@localhost.localdomain', $result->updatedArtisan->getContactInfoOriginal());
        self::assertEquals('E-MAIL', $result->updatedArtisan->getContactMethod());
        self::assertEquals('getfursu.it@localhost.localdomain', $result->updatedArtisan->getContactAddressPlain());
        self::assertEquals('E-MAIL: ge*******it@local***********omain', $result->updatedArtisan->getContactInfoObfuscated());
    }

    public function testAddedDateIsHandledProperly(): void
    {
        UtcClockMock::start();

        $submissionData = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
        );

        $subject = $this->getSetUpUpdatesService([
            [[''], ['MAKERID'], []],
        ]);
        $result = $subject->getUpdateFor(new UpdateInput($submissionData, new Submission()));

        self::assertEquals(null, $result->originalArtisan->getDateAdded());
        self::assertEquals(null, $result->originalArtisan->getDateUpdated());

        self::assertEquals(UtcClock::now(), $result->originalInput->getDateAdded());
        self::assertEquals(null, $result->originalInput->getDateUpdated());

        self::assertEquals(UtcClock::now(), $result->updatedArtisan->getDateAdded());
        self::assertEquals(null, $result->updatedArtisan->getDateUpdated());
    }

    /**
     * @throws SubmissionException|DateTimeException
     */
    public function testUpdatedDateIsHandledProperly(): void
    {
        UtcClockMock::start();

        $dateAdded = UtcClock::at('2022-09-09 09:09:09');

        $artisan = $this->getPersistedArtisanMock()
            ->setMakerId('MAKERID')
            ->setDateAdded($dateAdded)
        ;

        $submissionData = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
        );

        $subject = $this->getSetUpUpdatesService([
            [[''], ['MAKERID'], [$artisan]],
        ]);
        $result = $subject->getUpdateFor(new UpdateInput($submissionData, new Submission()));

        self::assertEquals($dateAdded, $result->originalArtisan->getDateAdded());
        self::assertEquals(null, $result->originalArtisan->getDateUpdated());

        self::assertEquals($dateAdded, $result->originalInput->getDateAdded());
        self::assertEquals(UtcClock::now(), $result->originalInput->getDateUpdated());

        self::assertEquals($dateAdded, $result->updatedArtisan->getDateAdded());
        self::assertEquals(UtcClock::now(), $result->updatedArtisan->getDateUpdated());
    }

    /**
     * @dataProvider imagesUpdateShouldResetMiniaturesDataProvider
     */
    public function testUpdateHandlesImagesUpdateProperly(string $initialUrlPhotos, string $initialMiniatures, string $newUrlPhotos, string $expectedMiniatures): void
    {
        $artisan = $this->getPersistedArtisanMock()
            ->setMakerId('MAKERID')
            ->setPhotoUrls($initialUrlPhotos)
            ->setMiniatureUrls($initialMiniatures)
        ;

        $submissionData = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
            ->setPhotoUrls($newUrlPhotos)
        );

        $subject = $this->getSetUpUpdatesService([
            [[''], ['MAKERID'], [$artisan]],
        ]);
        $result = $subject->getUpdateFor(new UpdateInput($submissionData, new Submission()));

        self::assertEquals($expectedMiniatures, $result->updatedArtisan->getMiniatureUrls());
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public function imagesUpdateShouldResetMiniaturesDataProvider(): array
    {
        return [
            'No photos at all'         => ['', '', '', ''],
            'No photos before, adding' => ['', '', 'NEW_PHOTOS', ''],
            'Clearing existing photos' => ['OLD_PHOTOS', 'OLD_MINIATURES', '', ''],
            'Changing photos'          => ['OLD_PHOTOS', 'OLD_MINIATURES', 'NEW_PHOTOS', ''],
            'Photos exist, unchanged'  => ['OLD_PHOTOS', 'OLD_MINIATURES', 'OLD_PHOTOS', 'OLD_MINIATURES'],
        ];
    }

    public function testResolvingMultipleMatchedByMakerId(): void
    {
        $artisan1 = $this->getPersistedArtisanMock()
            ->setMakerId('MAKER01')
            ->setName('Common name')
        ;

        $artisan2 = $this->getPersistedArtisanMock()
            ->setMakerId('MAKER02')
            ->setName('Common part')
        ;

        $submissionData = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Common')
        );

        $subject = $this->getSetUpUpdatesService([
            [['Common'], ['MAKERID'], [$artisan1, $artisan2]],
            [[], ['MAKER01'], [$artisan1]],
        ]);

        $result = $subject->getUpdateFor(new UpdateInput($submissionData, new Submission()));
        self::assertEquals([$artisan1, $artisan2], $result->matchedArtisans);

        $artisan1->getUrlObjs(Field::URL_OTHER); // Force initialization of URL accessor
        $result = $subject->getUpdateFor(new UpdateInput($submissionData, (new Submission())->setDirectives('match-maker-id MAKER01')));
        self::assertEquals([$artisan1], $result->matchedArtisans);
    }

    public function testUpdateHandlesMakerIdChangeProperly(): void
    {
        $artisan = $this->getPersistedArtisanMock()
            ->setMakerId('MAKERID')
            ->setFormerMakerIds('MAKER00')
            ->setName('The old maker name')
        ;

        // Changing
        $submissionData1 = Submissions::from(Artisan::new()
            ->setMakerId('MAKER22')
            ->setName('The new maker name')
            ->setFormerly('The old maker name')
        );

        $result1 = $this->getSetUpUpdatesService([
            [['The new maker name', 'The old maker name'], ['MAKER22'], [$artisan]],
        ])->getUpdateFor(new UpdateInput($submissionData1, new Submission()));

        self::assertEquals('The new maker name', $result1->updatedArtisan->getName());
        self::assertEquals('The old maker name', $result1->updatedArtisan->getFormerly());
        self::assertEquals('MAKER22', $result1->updatedArtisan->getMakerId());
        self::assertEquals(['MAKERID', 'MAKER00'], $result1->updatedArtisan->getFormerMakerIdsArr());

        // No change
        $submissionData2 = Submissions::from(Artisan::new()
            ->setMakerId('MAKERID')
            ->setName('The new maker name')
            ->setFormerly('The old maker name')
        );

        $result2 = $this->getSetUpUpdatesService([
            [['The new maker name', 'The old maker name'], ['MAKERID'], [$artisan]],
        ])->getUpdateFor(new UpdateInput($submissionData2, new Submission()));

        self::assertEquals('The new maker name', $result2->updatedArtisan->getName());
        self::assertEquals('The old maker name', $result2->updatedArtisan->getFormerly());
        self::assertEquals('MAKERID', $result2->updatedArtisan->getMakerId());
        self::assertEquals('MAKER00', $result2->updatedArtisan->getFormerMakerIds());
    }

    /**
     * @param list<array{list<string>, list<string>, list<Artisan>}> $calls
     */
    private function getSetUpUpdatesService(array $calls): UpdatesService
    {
        $artisanRepoMock = $this->createMock(ArtisanRepository::class);
        $artisanRepoMock->method('findBestMatches')->willReturnCallback(function (array $names, array $makerIds) use ($calls) {
            foreach ($calls as $call) {
                if ($call[0] === $names && $call[1] === $makerIds) {
                    return map($call[2], fn ($artisan) => $artisan->getArtisan());
                }
            }

            self::fail('findBestMatches was called with unexpected parameters');
        });

        $fixerMock = $this->createMock(Fixer::class);
        $fixerMock->method('getFixed')->willReturnArgument(0);

        return new UpdatesService($artisanRepoMock, $fixerMock);
    }
}
