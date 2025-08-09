<?php

declare(strict_types=1);

namespace App\Tests\Species;

use App\Entity\Specie;
use App\Repository\SpecieRepository;
use App\Species\DbSpeciesService;
use App\Species\SpecieException;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use Override;
use PHPUnit\Framework\Attributes\Medium;
use Psr\Log\LoggerInterface;
use Veelkoov\Debris\StringList;
use Veelkoov\Debris\StringSet;

#[Medium]
class DbSpeciesServiceTest extends FuzzrakeKernelTestCase
{
    private DbSpeciesService $subject;
    private SpecieRepository $speciesRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->speciesRepository = self::getContainerService(SpecieRepository::class);
        $this->subject = new DbSpeciesService(self::getEM(), $this->speciesRepository,
            self::getContainerService(LoggerInterface::class));
    }

    public function testAssureSpeciesWithGivenNamesExist(): void
    {
        $this->subject->assureSpeciesWithGivenNamesExist(StringSet::of('A', 'B'));
        self::flush();

        self::assertSameItems(['A', 'B'], $this->getExistingSpecieNames());

        $this->subject->assureSpeciesWithGivenNamesExist(StringSet::of('B', 'C'));
        self::flush();

        self::assertSameItems(['A', 'B', 'C'], $this->getExistingSpecieNames());
    }

    public function testRemoveSpeciesExceptForGivenNames(): void
    {
        self::persistAndFlush(
            new Specie()->setName('A'),
            new Specie()->setName('B'),
            new Specie()->setName('C'),
        );

        $this->subject->removeSpeciesExceptForGivenNames(StringSet::of('A', 'D'));
        self::flush();

        self::assertSameItems(['A'], $this->getExistingSpecieNames());

        $this->subject->removeSpeciesExceptForGivenNames(StringSet::of('C'));
        self::flush();

        self::assertSameItems([], $this->getExistingSpecieNames());
    }

    public function testGetSpecieByName(): void
    {
        self::persistAndFlush(new Specie()->setName('A'));

        $result = $this->subject->getSpecieByName('A');
        self::assertSame('A', $result->getName());

        self::expectException(SpecieException::class);
        self::expectExceptionMessage("Specie 'B' does not exist in the database.");
        $this->subject->getSpecieByName('B');
    }

    private function getExistingSpecieNames(): StringList
    {
        return StringList::mapFrom($this->speciesRepository->findAll(), static fn (Specie $specie) => $specie->getName());
    }
}
