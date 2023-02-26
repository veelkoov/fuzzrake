<?php

declare(strict_types=1);

namespace App\Tests\Data\Species;

use App\Data\Species\MissingSpecieException;
use App\Data\Species\Specie;
use App\Data\Species\SpeciesList;
use Exception;
use PHPUnit\Framework\TestCase;

class SpeciesListTest extends TestCase
{
    public function testGetAll(): void
    {
        $specie1 = new Specie('A', true);
        $specie2 = new Specie('A', false);
        $specie3 = new Specie('B', false);

        $subject = new SpeciesList();
        $subject->add($specie1);
        $subject->add($specie2, $specie3);

        self::assertEquals($specie2, $subject->getByName('A'));
        self::assertEquals($specie3, $subject->getByName('B'));
        self::assertCount(2, $subject->getAll());
    }

    public function testGetByNameOrCreate(): void
    {
        $specieA = new Specie('A', false);

        $subject = new SpeciesList();
        $subject->add($specieA);

        self::assertEquals($specieA, $subject->getByNameOrCreate('A', false));

        $specieB = $subject->getByNameOrCreate('B', true);
        self::assertEquals('B', $specieB->getName());
        self::assertEquals(true, $specieB->isHidden());
    }

    public function testGetByName(): void
    {
        $specie = new Specie('A', false);

        $subject = new SpeciesList();
        $subject->add($specie);

        self::assertEquals($specie, $subject->getByName('A'));

        $exception = null;
        try {
            $subject->getByName('B');
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(MissingSpecieException::class, $exception);
    }

    public function testGetNames(): void
    {
        $specieA = new Specie('A', false);
        $specieB = new Specie('B', false);
        $specieC = new Specie('C', false);
        $specieC->addParentTwoWay($specieB);

        $subject = new SpeciesList();
        $subject->add($specieA, $specieB);

        self::assertEquals(['A', 'B'], $subject->getNames());
    }
}
