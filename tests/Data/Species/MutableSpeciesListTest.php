<?php

declare(strict_types=1);

namespace App\Tests\Data\Species;

use App\Data\Species\Exceptions\MissingSpecieException;
use App\Data\Species\MutableSpecie;
use App\Data\Species\MutableSpeciesList;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class MutableSpeciesListTest extends TestCase
{
    public function testAddAndGetItems(): void
    {
        $specie1 = new MutableSpecie('A', true);
        $specie2 = new MutableSpecie('A', false);
        $specie3 = new MutableSpecie('B', false);

        $subject = new MutableSpeciesList();
        $subject->add($specie1);
        $subject->add($specie2, $specie3);

        self::assertEquals($specie2, $subject->getByName('A'));
        self::assertEquals($specie3, $subject->getByName('B'));
        self::assertCount(2, $subject->getItems());
    }

    public function testGetByNameOrCreate(): void
    {
        $specieA = new MutableSpecie('A', false);

        $subject = new MutableSpeciesList();
        $subject->add($specieA);

        self::assertEquals($specieA, $subject->getByNameOrCreate('A', false));

        $specieB = $subject->getByNameOrCreate('B', true);
        self::assertEquals('B', $specieB->name);
        self::assertTrue($specieB->isHidden());
    }

    public function testGetByName(): void
    {
        $specie = new MutableSpecie('A', false);

        $subject = new MutableSpeciesList();
        $subject->add($specie);

        self::assertEquals($specie, $subject->getByName('A'));

        $exception = null;
        try {
            $subject->getByName('B');
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(MissingSpecieException::class, $exception);
    }
}
