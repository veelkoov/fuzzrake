<?php

declare(strict_types=1);

namespace App\Tests\Tasks;

use App\DataDefinitions\Fields\Field;
use App\Entity\Artisan as ArtisanE;
use App\Repository\ArtisanRepository;
use App\Submissions\SubmissionData;
use App\Tasks\DataImport;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\FixerDifferValidator;
use App\Utils\Data\Manager;
use App\Utils\Data\Printer;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DataImportTest extends TestCase
{
    /**
     * @dataProvider imagesUpdateShouldResetMiniaturesDataProvider
     */
    public function testImagesUpdateShouldResetMiniatures(string $initialUrlPhotos, string $initialMiniatures, string $newUrlPhotos, string $expectedMiniatures): void
    {
        $artisan = $this->getBasicArtisan([
            Field::MAKER_ID->name       => 'MAKERID',
            Field::PASSWORD->name       => 'PASSWORD',
            Field::URL_PHOTOS->name     => $initialUrlPhotos,
            Field::URL_MINIATURES->name => $initialMiniatures,
        ]);

        $objectManager = $this->getObjectManagerMock($artisan);
        $importManager = $this->getImportManagerMock();
        $printer = $this->getPrinterMock();
        $fdv = $this->getFixerDifferValidatorMock();

        $dataImport = new DataImport($objectManager, $importManager, $printer, $fdv);
        $dataImport->import([$this->getIuSubmission($artisan, [
            Field::URL_PHOTOS->name => [$newUrlPhotos],
        ])]);

        static::assertEquals($expectedMiniatures, Artisan::wrap($artisan)->getMiniatureUrls());
    }

    public function imagesUpdateShouldResetMiniaturesDataProvider(): array // @phpstan-ignore-line
    {
        return [
            ['', '', '', ''],
            ['', '', 'NEW_PHOTOS', ''],
            ['OLD_PHOTOS', 'OLD_MINIATURES', '', ''],
            ['OLD_PHOTOS', 'OLD_MINIATURES', 'NEW_PHOTOS', ''],
            ['OLD_PHOTOS', 'OLD_MINIATURES', 'OLD_PHOTOS', 'OLD_MINIATURES'],
        ];
    }

    private function getObjectManagerMock(ArtisanE $artisan): EntityManagerInterface
    {
        $result = $this->createMock(EntityManagerInterface::class);
        $result->expects(self::once())->method('getRepository')->willReturn($this->getArtisanRepositoryMock($artisan));

        return $result;
    }

    /**
     * @param array<string, psJsonFieldValue> $initialData
     */
    private function getBasicArtisan(array $initialData): ArtisanE
    {
        $artisan = Artisan::wrap($result = new ArtisanE());

        foreach ($initialData as $fieldName => $value) {
            $artisan->set(Field::from($fieldName), $value);
        }

        return $result;
    }

    private function getImportManagerMock(): Manager
    {
        $result = $this->createMock(Manager::class);
        $result->expects(static::exactly(2))->method('isAccepted')->willReturn(true);

        return $result;
    }

    private function getPrinterMock(): Printer
    {
        // return new Printer(new SymfonyStyle(new StringInput(''), new DebugStdoutOutput()));
        return $this->createMock(Printer::class);
    }

    private function getFixerDifferValidatorMock(): FixerDifferValidator
    {
        return $this->createMock(FixerDifferValidator::class);
    }

    /**
     * @param array<string, psJsonFieldValue> $data
     */
    private function getIuSubmission(ArtisanE $artisan, array $data): SubmissionData
    {
        $allData = Artisan::wrap($artisan)->getAllData();
        $allData = array_merge($allData, $data);

        return new SubmissionData(new DateTimeImmutable(), 'test_id', $allData);
    }

    private function getArtisanRepositoryMock(ArtisanE $artisan): ArtisanRepository
    {
        $result = $this->createMock(ArtisanRepository::class);
        $result->expects(self::once())->method('findBestMatches')->willReturn([$artisan]);

        return $result;
    }
}
