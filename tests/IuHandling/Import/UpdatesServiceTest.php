<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\Entity\Artisan as ArtisanE;
use App\Entity\Submission;
use App\IuHandling\Exception\SubmissionException;
use App\IuHandling\Import\UpdateInput;
use App\IuHandling\Import\UpdatesService;
use App\Repository\ArtisanRepository;
use App\Tests\TestUtils\Cases\TestCase;
use App\Tests\TestUtils\Submissions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\Fixer;
use App\Utils\DateTime\UtcClock;
use App\Utils\TestUtils\UtcClockMock;
use Psr\Log\LoggerInterface;

use function Psl\Vec\map;

class UpdatesServiceTest extends TestCase
{
    public function testUpdateHandlesNewContactInfoProperly(): void
    {
        $submissionData = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
            ->setContactInfoObfuscated('getfursu.it@localhost.localdomain')
        );

        $subject = $this->getSetUpUpdatesService([]);
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

        $subject = $this->getSetUpUpdatesService([$artisan]);
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

        $subject = $this->getSetUpUpdatesService([$artisan]);
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

        $subject = $this->getSetUpUpdatesService([]);
        $result = $subject->getUpdateFor(new UpdateInput($submissionData, new Submission()));

        self::assertEquals(null, $result->originalArtisan->getDateAdded());
        self::assertEquals(null, $result->originalArtisan->getDateUpdated());

        self::assertEquals(UtcClock::now(), $result->originalInput->getDateAdded());
        self::assertEquals(null, $result->originalInput->getDateUpdated());

        self::assertEquals(UtcClock::now(), $result->updatedArtisan->getDateAdded());
        self::assertEquals(null, $result->updatedArtisan->getDateUpdated());
    }

    /**
     * @throws SubmissionException
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

        $subject = $this->getSetUpUpdatesService([$artisan]);
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

        $subject = $this->getSetUpUpdatesService([$artisan]);
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

    private function getPersistedArtisanMock(): Artisan
    {
        $result = $this->getMockBuilder(ArtisanE::class)->onlyMethods(['getId'])->getMock();
        $result->method('getId')->willReturn(1);

        return Artisan::wrap($result);
    }

    /**
     * @param Artisan[] $bestMatchesArtisans
     */
    private function getSetUpUpdatesService(array $bestMatchesArtisans): UpdatesService
    {
        $entities = map($bestMatchesArtisans, fn ($item) => $item->getArtisan());

        $artisanRepoMock = $this->createMock(ArtisanRepository::class);
        $artisanRepoMock->expects($this->once())->method('findBestMatches')->willReturn($entities);

        $fixerMock = $this->createMock(Fixer::class);
        $fixerMock->method('getFixed')->willReturnArgument(0);

        $loggerMock = $this->createMock(LoggerInterface::class);

        return new UpdatesService($loggerMock, $artisanRepoMock, $fixerMock);
    }
}
