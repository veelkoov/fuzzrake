<?php

declare(strict_types=1);

namespace App\Tests\Species\Hierarchy;

use App\Species\Hierarchy\MutableSpecie;
use App\Species\SpecieException;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class MutableSpecieTest extends TestCase
{
    public function testRelationshipIsBeingSetTwoWay(): void
    {
        $parent = new MutableSpecie('Parent A', false);
        $child = new MutableSpecie('Child A', false);

        $parent->addChild($child);

        self::assertContains($parent, $child->getParents());
        self::assertContains($child, $parent->getChildren());
    }

    public function testGetParentsAndGetAncestorsWorks(): void
    {
        $top1a = new MutableSpecie('Top 1A', false);
        $top1b = new MutableSpecie('Top 1B', false);
        $top2a = new MutableSpecie('Top 2A', false);
        $top2b = new MutableSpecie('Top 2B', false);
        $middle1 = new MutableSpecie('Middle 1', false);
        $middle2 = new MutableSpecie('Middle 2', false);
        $bottom = new MutableSpecie('Bottom', false);

        $middle1->addChild($bottom);
        $middle2->addChild($bottom);

        $top1a->addChild($middle1);
        $top1b->addChild($middle1);

        $top2a->addChild($middle2);
        $top2b->addChild($middle2);

        self::assertEqualsCanonicalizing(
            ['Top 1A', 'Top 1B'],
            $middle1->getParents()->getNames()->getValuesArray(),
        );
        self::assertEquals(
            $middle1->getParents()->getValuesArray(),
            $middle1->getAncestors()->getValuesArray(),
        );
        self::assertEqualsCanonicalizing(
            ['Top 1A', 'Top 1B', 'Top 2A', 'Top 2B', 'Middle 1', 'Middle 2'],
            $bottom->getAncestors()->getNames()->getValuesArray(),
        );
        self::assertEqualsCanonicalizing(
            ['Top 1A', 'Top 1B', 'Top 2A', 'Top 2B', 'Middle 1', 'Middle 2', 'Bottom'],
            $bottom->getThisAndAncestors()->getNames()->getValuesArray(),
        );
    }

    public function testGetChildrenAndGetDescendants(): void
    {
        $top = new MutableSpecie('Top', false);
        $middle1 = new MutableSpecie('Middle 1', false);
        $middle2 = new MutableSpecie('Middle 2', false);
        $bottom1a = new MutableSpecie('Bottom 1A', false);
        $bottom1b = new MutableSpecie('Bottom 1B', false);
        $bottom2a = new MutableSpecie('Bottom 2A', false);
        $bottom2b = new MutableSpecie('Bottom 2B', false);

        $top->addChild($middle1);
        $top->addChild($middle2);

        $middle1->addChild($bottom1a);
        $middle1->addChild($bottom1b);

        $middle2->addChild($bottom2a);
        $middle2->addChild($bottom2b);

        self::assertEqualsCanonicalizing(
            ['Bottom 1A', 'Bottom 1B'],
            $middle1->getChildren()->getNames()->getValuesArray(),
        );
        self::assertEquals(
            $middle1->getChildren()->getValuesArray(),
            $middle1->getDescendants()->getValuesArray(),
        );
        self::assertEqualsCanonicalizing(
            ['Middle 1', 'Middle 2', 'Bottom 1A', 'Bottom 1B', 'Bottom 2A', 'Bottom 2B'],
            $top->getDescendants()->getNames()->getValuesArray(),
        );
        self::assertEqualsCanonicalizing(
            ['Top', 'Middle 1', 'Middle 2', 'Bottom 1A', 'Bottom 1B', 'Bottom 2A', 'Bottom 2B'],
            $top->getThisAndDescendants()->getNames()->getValuesArray(),
        );
    }

    public function testCannotRecurseItself(): void
    {
        $specie = new MutableSpecie('Test specie', false);

        self::expectException(SpecieException::class);
        self::expectExceptionMessage("Cannot add 'Test specie' as a child of itself");

        $specie->addChild($specie);
    }

    public function testCannotRecurseWithMultipleSteps(): void
    {
        $specieA = new MutableSpecie('Test specie A', false);
        $specieB = new MutableSpecie('Test specie B', false);
        $specieC = new MutableSpecie('Test specie C', false);

        $specieA->addChild($specieB);
        $specieB->addChild($specieC);

        try {
            $specieB->addChild($specieA);
        } catch (SpecieException $exception) {
            self::assertEquals("Recursion when adding child 'Test specie A' to 'Test specie B'", $exception->getMessage());
        }

        try {
            $specieC->addChild($specieA);
        } catch (SpecieException $exception) {
            self::assertEquals("Recursion when adding child 'Test specie A' to 'Test specie C'", $exception->getMessage());
        }

        try {
            $specieC->addChild($specieB);
        } catch (SpecieException $exception) {
            self::assertEquals("Recursion when adding child 'Test specie B' to 'Test specie C'", $exception->getMessage());
        }
    }

    public function testDepthCalculation(): void
    {
        // A
        // |
        // B
        // |\
        // C |
        // |/
        // D

        $specieA = new MutableSpecie('Test specie A', false);
        $specieB = new MutableSpecie('Test specie B', false);
        $specieC = new MutableSpecie('Test specie C', false);
        $specieD = new MutableSpecie('Test specie D', false);

        $specieA->addChild($specieB);
        $specieB->addChild($specieC);
        $specieB->addChild($specieD);
        $specieC->addChild($specieD);

        self::assertEquals(0, $specieA->getDepth());
        self::assertEquals(1, $specieB->getDepth());
        self::assertEquals(2, $specieC->getDepth());
        self::assertEquals(3, $specieD->getDepth());
    }
}
