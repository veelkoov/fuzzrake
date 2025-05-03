<?php

declare(strict_types=1);

namespace App\Tests\Species\Hierarchy;

use App\Species\Hierarchy\MutableSpecies;
use App\Species\SpecieException;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class MutableSpeciesTest extends TestCase
{
    public function testGetNames(): void
    {
        $subject = $this->getTestInstance();

        self::assertEqualsCanonicalizing(
            ['Root 1', 'Middle 1', 'Leaf 1A', 'Leaf 1B', 'Root 2', 'Leaf 2'],
            $subject->getNames()->getValuesArray(),
        );
    }

    public function testGetVisibleNames(): void
    {
        $subject = $this->getTestInstance();

        self::assertEqualsCanonicalizing(
            ['Root 1', 'Middle 1', 'Leaf 1A', 'Leaf 1B'],
            $subject->getVisibleNames()->getValuesArray(),
        );
    }

    public function testGetAsTree(): void
    {
        $subject = $this->getTestInstance();

        self::assertEqualsCanonicalizing(
            ['Root 1', 'Root 2'],
            $subject->getAsTree()->getNames()->getValuesArray(),
        );
    }

    public function testGetByName(): void
    {
        $subject = $this->getTestInstance();

        self::assertSame('Leaf 1B', $subject->getByName('Leaf 1B')->getName());
        try {
            $subject->getByName('Middle 2');
        } catch (SpecieException) {
            // Expected
        }
    }

    public function testHasName(): void
    {
        $subject = $this->getTestInstance();

        self::assertTrue($subject->hasName('Leaf 1B'));
        self::assertFalse($subject->hasName('Middle 2'));
    }

    private function getTestInstance(): MutableSpecies
    {
        $result = new MutableSpecies();

        $root1 = $result->getByNameCreatingMissing('Root 1', false);
        $middle1 = $result->getByNameCreatingMissing('Middle 1', false);
        $root1->addChild($middle1);

        $leaf1a = $result->getByNameCreatingMissing('Leaf 1A', false);
        $leaf1b = $result->getByNameCreatingMissing('Leaf 1B', false);
        $middle1->addChild($leaf1a);
        $middle1->addChild($leaf1b);

        $root2 = $result->getByNameCreatingMissing('Root 2', true);
        $leaf2 = $result->getByNameCreatingMissing('Leaf 2', true);
        $root2->addChild($leaf2);

        $result->addRootSpecie($root1);
        $result->addRootSpecie($root2);

        return $result;
    }
}
