<?php

declare(strict_types=1);

namespace App\Tests\Utils\Species;

use App\Utils\Species\Specie;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

/**
 * @small
 */
class SpecieTest extends TestCase
{
    public function testChildrenParentsAncestorsDescendantsRootsAndLeavesWork(): void
    {
        $root1 = new Specie('Root1', false);
        $root2 = new Specie('Root2', false);
        $middle = new Specie('Middle', false);
        $leaf1 = new Specie('Leaf1', false);
        $leaf2 = new Specie('Leaf2', false);

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

        $children = Vec\map($middle->getChildren(), fn (Specie $specie) => $specie->getName());
        self::assertEquals(['Leaf1', 'Leaf2'], $children);

        $parents = Vec\map($middle->getParents(), fn (Specie $specie) => $specie->getName());
        self::assertEquals(['Root1', 'Root2'], $parents);

        $ancestors = Vec\map($leaf1->getAncestors(), fn (Specie $specie) => $specie->getName());
        self::assertEquals(['Middle', 'Root1', 'Root2'], $ancestors);

        $ancestors = Vec\map($leaf1->getSelfAndAncestors(), fn (Specie $specie) => $specie->getName());
        self::assertEquals(['Leaf1', 'Middle', 'Root1', 'Root2'], $ancestors);

        $descendants = Vec\map($root1->getDescendants(), fn (Specie $specie) => $specie->getName());
        self::assertEquals(['Middle', 'Leaf1', 'Leaf2'], $descendants);

        $descendants = Vec\map($root1->getSelfAndDescendants(), fn (Specie $specie) => $specie->getName());
        self::assertEquals(['Root1', 'Middle', 'Leaf1', 'Leaf2'], $descendants);
    }

    public function testRecursionSafetyInAddChild(): void
    {
        $a = new Specie('a', false);
        $b = new Specie('b', false);
        $c = new Specie('c', false);

        $exception = null;
        try {
            $a->addChild($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(InvalidArgumentException::class, $exception);

        $a->addChild($b);

        $exception = null;
        try {
            $b->addChild($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(InvalidArgumentException::class, $exception);

        $a->addChild($b);
        $b->addChild($c);

        $exception = null;
        try {
            $c->addChild($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    public function testRecursionSafetyInAddParent(): void
    {
        $a = new Specie('a', false);
        $b = new Specie('b', false);
        $c = new Specie('c', false);

        $exception = null;
        try {
            $a->addParent($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(InvalidArgumentException::class, $exception);

        $a->addParent($b);

        $exception = null;
        try {
            $b->addParent($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(InvalidArgumentException::class, $exception);

        $a->addParent($b);
        $b->addParent($c);

        $exception = null;
        try {
            $c->addParent($a);
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
