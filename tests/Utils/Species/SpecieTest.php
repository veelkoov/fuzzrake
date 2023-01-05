<?php

declare(strict_types=1);

namespace App\Tests\Utils\Species;

use App\Utils\Species\Specie;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

/**
 * @small
 */
class SpecieTest extends TestCase
{
    public function testChildrenParentsAncestorsAndDescendants(): void
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

        $children = Vec\map($middle->getChildren(), fn (Specie $specie) => $specie->getName());
        self::assertEquals(['Leaf1', 'Leaf2'], $children);

        $parents = Vec\map($middle->getParents(), fn (Specie $specie) => $specie->getName());
        self::assertEquals(['Root1', 'Root2'], $parents);

        $ancestors = Vec\map($leaf1->getAncestors(), fn (Specie $specie) => $specie->getName());
        self::assertEquals(['Middle', 'Root1', 'Root2'], $ancestors);

        $descendants = Vec\map($root1->getDescendants(), fn (Specie $specie) => $specie->getName());
        self::assertEquals(['Middle', 'Leaf1', 'Leaf2'], $descendants);
    }
}
