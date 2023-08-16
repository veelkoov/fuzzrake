<?php

declare(strict_types=1);

namespace App\Tests\Data\Species;

use App\Data\Species\Exceptions\IncompleteSpecieException;
use App\Data\Species\Specie;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

/**
 * @small
 */
class SpecieTest extends TestCase
{
    public function testChildrenParentsAncestorsDescendantsRootsAndLeavesWork(): void
    {
        $root1 = new Specie('Root1', false, 0);
        $root2 = new Specie('Root2', false, 0);
        $middle = new Specie('Middle', false, 1);
        $leaf1 = new Specie('Leaf1', false, 2);
        $leaf2 = new Specie('Leaf2', false, 2);

        $root1->setParents([]);
        $root2->setParents([]);
        $root1->setChildren([$middle]);
        $root2->setChildren([$middle]);

        $middle->setChildren([$leaf1, $leaf2]);
        $middle->setParents([$root1, $root2]);

        $leaf1->setParents([$middle]);
        $leaf2->setParents([$middle]);
        $leaf1->setChildren([]);
        $leaf2->setChildren([]);

        self::assertFalse($middle->isRoot());
        self::assertFalse($middle->isLeaf());
        self::assertTrue($root1->isRoot());
        self::assertFalse($root1->isLeaf());
        self::assertFalse($leaf1->isRoot());
        self::assertTrue($leaf1->isLeaf());

        $children = Vec\map($middle->getChildren(), fn (Specie $specie) => $specie->name);
        self::assertEquals(['Leaf1', 'Leaf2'], $children);

        $parents = Vec\map($middle->getParents(), fn (Specie $specie) => $specie->name);
        self::assertEquals(['Root1', 'Root2'], $parents);

        $ancestors = Vec\map($leaf1->getAncestors(), fn (Specie $specie) => $specie->name);
        self::assertEquals(['Middle', 'Root1', 'Root2'], $ancestors);

        $ancestors = Vec\map($leaf1->getSelfAndAncestors(), fn (Specie $specie) => $specie->name);
        self::assertEquals(['Leaf1', 'Middle', 'Root1', 'Root2'], $ancestors);

        $descendants = Vec\map($root1->getDescendants(), fn (Specie $specie) => $specie->name);
        self::assertEquals(['Middle', 'Leaf1', 'Leaf2'], $descendants);

        $descendants = Vec\map($root1->getSelfAndDescendants(), fn (Specie $specie) => $specie->name);
        self::assertEquals(['Root1', 'Middle', 'Leaf1', 'Leaf2'], $descendants);
    }

    public function testStringable(): void
    {
        $subject = new Specie('A specie', false, 0);

        $this->assertEquals('A specie', (string) $subject);
    }

    public function testhandlingUninitializedChildren(): void
    {
        $subject = new Specie('A specie', false, 0);
        $subject->setParents([]);

        $subject->isRoot();

        $this->expectException(IncompleteSpecieException::class);
        $subject->isLeaf();
    }

    public function testhandlingUninitializedParents(): void
    {
        $subject = new Specie('A specie', false, 0);
        $subject->setChildren([]);

        $subject->isLeaf();

        $this->expectException(IncompleteSpecieException::class);
        $subject->isRoot();
    }
}
