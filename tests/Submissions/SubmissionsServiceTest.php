<?php

declare(strict_types=1);

namespace App\Tests\Submissions;

use App\Entity\Artisan as ArtisanE;
use App\Repository\ArtisanRepository;
use App\Submissions\SubmissionsService;
use App\Tests\TestUtils\Submissions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\Fixer;
use PHPUnit\Framework\TestCase;

class SubmissionsServiceTest extends TestCase
{
    public function testUpdateHandlesNewContactInfoProperly(): void
    {
        $artisanRepoMock = $this->getArtisanRepoMock([]);
        $fixerMock = $this->getFixerMock();

        $submission = Submissions::from((new Artisan())
            ->setContactInfoObfuscated('getfursu.it@localhost.localdomain')
        );

        $subject = new SubmissionsService($artisanRepoMock, $fixerMock, '');
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
        $artisan = $this->getArtisanMock('getfursu.it@localhost.localdomain');

        $artisanRepoMock = $this->getArtisanRepoMock([$artisan->getArtisan()]);
        $fixerMock = $this->getFixerMock();

        $submission = Submissions::from((new Artisan())
            ->setContactInfoObfuscated('Telegram: @getfursuit'));

        $subject = new SubmissionsService($artisanRepoMock, $fixerMock, '');
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
        $artisan = $this->getArtisanMock('getfursu.it@localhost.localdomain');

        $artisanRepoMock = $this->getArtisanRepoMock([$artisan->getArtisan()]);
        $fixerMock = $this->getFixerMock();

        $submission = Submissions::from((new Artisan())
            ->setContactInfoObfuscated('E-MAIL: ge*******it@local***********omain'));

        $subject = new SubmissionsService($artisanRepoMock, $fixerMock, '');
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

    /**
     * @param ArtisanE[] $artisans
     */
    private function getArtisanRepoMock(array $artisans): ArtisanRepository
    {
        $artisanRepoMock = $this->createMock(ArtisanRepository::class);
        $artisanRepoMock->expects($this->once())->method('findBestMatches')->willReturn($artisans);

        return $artisanRepoMock;
    }

    private function getFixerMock(): Fixer
    {
        $fixerMock = $this->createMock(Fixer::class);
        $fixerMock->method('getFixed')->willReturnArgument(0);

        return $fixerMock;
    }

    /** @noinspection PhpSameParameterValueInspection */
    private function getArtisanMock(string $originalContactValue): Artisan
    {
        $entity = $this->getMockBuilder(ArtisanE::class)->onlyMethods(['getId'])->getMock();
        $entity->method('getId')->willReturn(1);

        $result = new Artisan($entity);
        $result->updateContact($originalContactValue);

        return $result;
    }
}
