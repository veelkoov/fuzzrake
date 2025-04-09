<?php

declare(strict_types=1);

namespace App\Tests\Species;

use App\Species\Hierarchy\Species;
use App\Species\SpecieException;
use App\Species\SpeciesService;
use App\Tests\TestUtils\DataDefinitions;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class SpeciesServiceTest extends TestCase
{
    private Species $subject;

    protected function setUp(): void
    {
        $this->subject = (new SpeciesService([
            'valid_choices' => [
                'Most species' => [
                    'Mammals' => [
                        'Felines' => [
                            'Panthers' => [],
                        ],
                        'Deer' => [],
                    ],
                    'With antlers' => [
                        'Deer' => [ // Add second parent
                            'i_Some deer specie' => [], // Add child to something already created
                        ],
                    ],
                ],
                'Other' => [
                    'i_Other 1' => [],
                    'i_Other 2' => [],
                ],
                'Third root' => [],
            ],
            'replacements' => [],
            'regex_prefix' => '',
            'regex_suffix' => '',
            'leave_unchanged' => [],
        ]))->species;
    }

    public function testTreeRootsAreAsExpected(): void
    {
        self::assertEqualsCanonicalizing(
            ['Most species', 'Other', 'Third root'],
            $this->subject->getAsTree()->getNames()->getValuesArray(),
        );
    }

    public function testThirdRootHasNoConnections(): void
    {
        self::assertEquals(0, $this->subject->getByName('Third root')->getParents()->count());
        self::assertEquals(0, $this->subject->getByName('Third root')->getChildren()->count());
    }

    public function testExceptionThrownForNonexistentSpecie(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $this->subject->getByName('Nonexistent');
        } catch (SpecieException) {
            // Expected
        }
    }

    public function testFamilyProperlyConstructed(): void
    {
        self::assertEqualsCanonicalizing(
            ['Felines', 'Panthers', 'Deer', 'Some deer specie'],
            $this->subject->getByName('Mammals')->getDescendants()->getNames()->getValuesArray(),
        );
    }

    public function testHiddenSpeciesAreHidden(): void
    {
        self::assertEqualsCanonicalizing(
            ['Some deer specie', 'Other 1', 'Other 2'],
            $this->subject->getNames()->minusAll($this->subject->getVisibleNames())->getValuesArray(),
        );
    }

    public function testBuiltInSpeciesLoads(): void
    {
        $this->expectNotToPerformAssertions();

        $speciesDefinitions = DataDefinitions::get('species.yaml', 'species_definitions');
        new SpeciesService($speciesDefinitions); // @phpstan-ignore argument.type
    }
}
