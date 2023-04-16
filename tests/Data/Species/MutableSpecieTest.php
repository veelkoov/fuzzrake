<?php

declare(strict_types=1);

namespace App\Tests\Data\Species;

use App\Data\Species\Exceptions\RecursionSpecieException;
use App\Data\Species\MutableSpecie;
use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

/**
 * @small
 */
class MutableSpecieTest extends TestCase
{
    public function testChildrenParentsAncestorsDescendantsRootsAndLeavesWork(): void
    {
        $root1 = new MutableSpecie('Root1', false);
        $root2 = new MutableSpecie('Root2', false);
        $middle = new MutableSpecie('Middle', false);
        $leaf1 = new MutableSpecie('Leaf1', false);
        $leaf2 = new MutableSpecie('Leaf2', false);

        $root1->addChild($middle);
        $root2->addChild($middle);
        $middle->addChild($leaf1);
        $middle->addChild($leaf2);
        $leaf1->addParent($middle);
        $leaf2->addParent($middle);
        $middle->addParent($root1);
        $middle->addParent($root2);

        self::assertFalse($middle->isRoot());
        self::assertFalse($middle->isLeaf());
        self::assertTrue($root1->isRoot());
        self::assertFalse($root1->isLeaf());
        self::assertFalse($leaf1->isRoot());
        self::assertTrue($leaf1->isLeaf());

        $children = Vec\map($middle->getChildren(), fn (MutableSpecie $specie) => $specie->name);
        self::assertEquals(['Leaf1', 'Leaf2'], $children);

        $parents = Vec\map($middle->getParents(), fn (MutableSpecie $specie) => $specie->name);
        self::assertEquals(['Root1', 'Root2'], $parents);

        $ancestors = Vec\map($leaf1->getAncestors(), fn (MutableSpecie $specie) => $specie->name);
        self::assertEquals(['Middle', 'Root1', 'Root2'], $ancestors);

        $ancestors = Vec\map($leaf1->getSelfAndAncestors(), fn (MutableSpecie $specie) => $specie->name);
        self::assertEquals(['Leaf1', 'Middle', 'Root1', 'Root2'], $ancestors);

        $descendants = Vec\map($root1->getDescendants(), fn (MutableSpecie $specie) => $specie->name);
        self::assertEquals(['Middle', 'Leaf1', 'Leaf2'], $descendants);

        $descendants = Vec\map($root1->getSelfAndDescendants(), fn (MutableSpecie $specie) => $specie->name);
        self::assertEquals(['Root1', 'Middle', 'Leaf1', 'Leaf2'], $descendants);
    }

    public function testRecursionSafetyInAddChild(): void
    {
        $a = new MutableSpecie('a', false);
        $b = new MutableSpecie('b', false);
        $c = new MutableSpecie('c', false);

        $exception = null;
        try {
            $a->addChild($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(RecursionSpecieException::class, $exception);

        $a->addChild($b);

        $exception = null;
        try {
            $b->addChild($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(RecursionSpecieException::class, $exception);

        $a->addChild($b);
        $b->addChild($c);

        $exception = null;
        try {
            $c->addChild($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(RecursionSpecieException::class, $exception);
    }

    public function testRecursionSafetyInAddParent(): void
    {
        $a = new MutableSpecie('a', false);
        $b = new MutableSpecie('b', false);
        $c = new MutableSpecie('c', false);

        $exception = null;
        try {
            $a->addParent($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(RecursionSpecieException::class, $exception);

        $a->addParent($b);

        $exception = null;
        try {
            $b->addParent($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(RecursionSpecieException::class, $exception);

        $a->addParent($b);
        $b->addParent($c);

        $exception = null;
        try {
            $c->addParent($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(RecursionSpecieException::class, $exception);
    }

    public function testStringable(): void
    {
        $subject = new MutableSpecie('A specie', false);

        $this->assertEquals('A specie', (string) $subject);
    }
}
