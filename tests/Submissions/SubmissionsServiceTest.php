<?php

declare(strict_types=1);

namespace App\Tests\Submissions;

use App\Entity\Artisan as ArtisanE;
use App\Repository\ArtisanRepository;
use App\Submissions\SubmissionsService;
use App\Tests\TestUtils\Cases\TestCase;
use App\Tests\TestUtils\Submissions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\Fixer;
use App\Utils\Data\Manager;
use App\Utils\DateTime\UtcClock;
use App\Utils\TestUtils\UtcClockMock;

use function Psl\Vec\map;

class SubmissionsServiceTest extends TestCase
{
    public function testUpdateHandlesNewContactInfoProperly(): void
    {
        $submission = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
            ->setContactInfoObfuscated('getfursu.it@localhost.localdomain')
        );

        $subject = $this->getSetUpSubmissionsService([]);
        $result = $subject->getUpdate($submission);

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

        $submission = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
            ->setContactInfoObfuscated('Telegram: @getfursuit')
        );

        $subject = $this->getSetUpSubmissionsService([$artisan]);
        $result = $subject->getUpdate($submission);

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

        $submission = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
            ->setContactInfoObfuscated('E-MAIL: ge*******it@local***********omain')
        );

        $subject = $this->getSetUpSubmissionsService([$artisan]);
        $result = $subject->getUpdate($submission);

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

        $submission = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
        );

        $subject = $this->getSetUpSubmissionsService([]);
        $result = $subject->getUpdate($submission);

        self::assertEquals(null, $result->originalArtisan->getDateAdded());
        self::assertEquals(null, $result->originalArtisan->getDateUpdated());

        self::assertEquals(UtcClock::now(), $result->originalInput->getDateAdded());
        self::assertEquals(null, $result->originalInput->getDateUpdated());

        self::assertEquals(UtcClock::now(), $result->updatedArtisan->getDateAdded());
        self::assertEquals(null, $result->updatedArtisan->getDateUpdated());
    }

    public function testUpdatedDateIsHandledProperly(): void
    {
        UtcClockMock::start();

        $artisan = $this->getPersistedArtisanMock()
            ->setMakerId('MAKERID')
        ;

        $submission = Submissions::from((new Artisan())
            ->setMakerId('MAKERID')
        );

        $subject = $this->getSetUpSubmissionsService([$artisan]);
        $result = $subject->getUpdate($submission);

        self::assertEquals(null, $result->originalArtisan->getDateAdded());
        self::assertEquals(null, $result->originalArtisan->getDateUpdated());

        self::assertEquals(null, $result->originalInput->getDateAdded());
        self::assertEquals(UtcClock::now(), $result->originalInput->getDateUpdated());

        self::assertEquals(null, $result->updatedArtisan->getDateAdded());
        self::assertEquals(UtcClock::now(), $result->updatedArtisan->getDateUpdated());
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
    private function getSetUpSubmissionsService(array $bestMatchesArtisans): SubmissionsService
    {
        $entities = map($bestMatchesArtisans, fn ($item) => $item->getArtisan());

        $artisanRepoMock = $this->createMock(ArtisanRepository::class);
        $artisanRepoMock->expects($this->once())->method('findBestMatches')->willReturn($entities);

        $fixerMock = $this->createMock(Fixer::class);
        $fixerMock->method('getFixed')->willReturnArgument(0);

        $manager = new Manager('');

        return new SubmissionsService($artisanRepoMock, $fixerMock, $manager, '');
    }
}
