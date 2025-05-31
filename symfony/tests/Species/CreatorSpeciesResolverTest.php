<?php

declare(strict_types=1);

namespace App\Tests\Species;

use App\Species\CreatorSpeciesResolver;
use App\Species\Hierarchy\MutableSpecies;
use App\Species\Hierarchy\Specie;
use App\Species\Hierarchy\Species;
use App\Utils\Collections\StringList;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider as TestDataProvider;
use Veelkoov\Debris\StringSet;

#[Small]
class CreatorSpeciesResolverTest extends TestCase
{
    /**
     * - Most species
     *   - Hidden
     * - Other.
     */
    private function getBasicSpecies(): Species
    {
        $species = new MutableSpecies();
        $mostSpecies = $species->getByNameCreatingMissing('Most species', false);
        $hidden = $species->getByNameCreatingMissing('Hidden', true);
        $mostSpecies->addChild($hidden);
        $other = $species->getByNameCreatingMissing('Other', false);

        $species->addRootSpecie($mostSpecies);
        $species->addRootSpecie($other);

        return $species;
    }

    public function testEmptyBothDoesAndDoesntReturnsEmptySet(): void
    {
        $subject = new CreatorSpeciesResolver($this->getBasicSpecies());

        $result = $subject->resolveDoes(new StringList(), new StringList());
        self::assertSame(0, $result->count());
    }

    public function testEmptyDoesAndUnknownDoesntReturnMostSpeciesOnly(): void
    {
        $subject = new CreatorSpeciesResolver($this->getBasicSpecies());

        $result = $subject->resolveDoes(new StringList(), StringList::of('Some unusual specie'));
        self::assertEqualsCanonicalizing(['Most species'], $result->getValuesArray());
    }

    public function testHiddenSpeciesAreNotReturned(): void
    {
        $subject = new CreatorSpeciesResolver($this->getBasicSpecies());

        $result = $subject->resolveDoes(StringList::of('Most species'), new StringList());

        self::assertTrue($result->contains('Most species'));
        self::assertFalse($result->contains('Hidden'));
    }

    public function testOtherSpeciesAreSimplified(): void
    {
        $subject = new CreatorSpeciesResolver($this->getBasicSpecies());

        $result = $subject->resolveDoes(StringList::of('Some weird specie'), new StringList());

        self::assertTrue($result->contains('Other'));
        self::assertFalse($result->contains('Some weird specie'));
    }

    /**
     * - Most species
     *   - A
     *     - B
     *       - C
     *         - D
     * - Other.
     */
    private function getGetOrderedDoesDoesntSpecies(): Species
    {
        $species = new MutableSpecies();

        $mostSpecies = $species->getByNameCreatingMissing('Most species', false);

        $a = $species->getByNameCreatingMissing('A', false);
        $b = $species->getByNameCreatingMissing('B', false);
        $c = $species->getByNameCreatingMissing('C', false);
        $d = $species->getByNameCreatingMissing('D', false);

        $mostSpecies->addChild($a);
        $a->addChild($b);
        $b->addChild($c);
        $c->addChild($d);

        $other = $species->getByNameCreatingMissing('Other', false);

        $species->addRootSpecie($mostSpecies);
        $species->addRootSpecie($other);

        return $species;
    }

    public static function getOrderedDoesDoesntSpeciesDataProvider(): TestDataProvider
    {
        return TestDataProvider::tuples(
            [StringList::of('A', 'C'), StringList::of('B', 'D'), '+A -B +C -D'],
            [StringList::of('C', 'A'), StringList::of('D', 'B'), '+A -B +C -D'],
            [StringList::of('B', 'D'), StringList::of('A', 'C'), '-A +B -C +D'],
            [StringList::of('D', 'B'), StringList::of('C', 'A'), '-A +B -C +D'],
        );
    }

    #[DataProvider('getOrderedDoesDoesntSpeciesDataProvider')]
    public function testGetOrderedDoesDoesnt(StringList $does, StringList $doesnt, string $expected): void
    {
        $subject = new CreatorSpeciesResolver($this->getGetOrderedDoesDoesntSpecies());

        $result = $subject->getOrderedDoesDoesnt($does, $doesnt);
        $strResult = StringList::mapFrom($result,
            static fn (bool $does, Specie $specie) => ($does ? '+' : '-').$specie->getName())->join(' ');

        self::assertSame($expected, $strResult);
    }

    /**
     * - Most species
     *   - Mammals
     *     - Canines
     *       - Dogs
     *         - Corgis
     *         - Dalmatians
     *       - Wolves
     *     - Deers
     *   - With antlers
     *     - Deers
     * - Other.
     */
    private function getResolveDoesSpecies(): Species
    {
        $species = new MutableSpecies();
        $mostSpecies = $species->getByNameCreatingMissing('Most species', false);
        $mammals = $species->getByNameCreatingMissing('Mammals', false);
        $withAntlers = $species->getByNameCreatingMissing('With antlers', false);
        $canines = $species->getByNameCreatingMissing('Canines', false);
        $dogs = $species->getByNameCreatingMissing('Dogs', false);
        $corgis = $species->getByNameCreatingMissing('Corgis', false);
        $dalmatians = $species->getByNameCreatingMissing('Dalmatians', false);
        $wolves = $species->getByNameCreatingMissing('Wolves', false);
        $deers = $species->getByNameCreatingMissing('Deers', false);
        $mostSpecies->addChild($mammals);
        $mostSpecies->addChild($withAntlers);
        $mammals->addChild($canines);
        $canines->addChild($dogs);
        $dogs->addChild($corgis);
        $dogs->addChild($dalmatians);
        $canines->addChild($wolves);
        $mammals->addChild($deers);
        $withAntlers->addChild($deers);
        $other = $species->getByNameCreatingMissing('Other', false);

        $species->addRootSpecie($mostSpecies);
        $species->addRootSpecie($other);

        return $species;
    }

    public static function resolveDoesDataProvider(): TestDataProvider
    {
        return TestDataProvider::tuples(
            [new StringList(),                       new StringList(),                          new StringList()],
            [StringList::of('Mammals', 'Corgis'),    StringList::of('Canines', 'With antlers'), StringList::of('Mammals', 'Corgis')],
            [StringList::of('Mammals'),              StringList::of('With antlers', 'Dogs'),    StringList::of('Mammals', 'Canines', 'Wolves')],
            [StringList::of('Mammals', 'Deers'),     StringList::of('With antlers', 'Dogs'),    StringList::of('Mammals', 'Canines', 'Wolves', 'Deers')],
            [StringList::of('Dogs', 'With antlers'), StringList::of(''),                        StringList::of('With antlers', 'Deers', 'Dogs', 'Corgis', 'Dalmatians')],
            [StringList::of('Dogs', 'With antlers'), StringList::of('Deers'),                   StringList::of('With antlers', 'Dogs', 'Corgis', 'Dalmatians')],
            [StringList::of('Dogs', 'Pancakes'),     StringList::of(''),                        StringList::of('Other', 'Dogs', 'Corgis', 'Dalmatians')],
            [StringList::of('Dogs', 'Other'),        StringList::of('Dalmatians'),              StringList::of('Other', 'Dogs', 'Corgis')],
        );
    }

    #[DataProvider('resolveDoesDataProvider')]
    public function testResolveDoes(StringList $does, StringList $doesnt, StringList $expected): void
    {
        $subject = new CreatorSpeciesResolver($this->getResolveDoesSpecies());

        $result = $subject->resolveDoes($does, $doesnt);

        self::assertEqualsCanonicalizing($expected->getValuesArray(), $result->getValuesArray());
    }

    public static function resolveForFiltersDataProvider(): TestDataProvider
    {
        return TestDataProvider::tuples(
            [new StringSet(), new StringSet()],
            [StringSet::of('Deers'),        StringSet::of('Deers', 'With antlers', 'Most species', 'Mammals')],
            [StringSet::of('With antlers'), StringSet::of('With antlers', 'Most species')],
            [StringSet::of('Other'),        StringSet::of('Other')],
        );
    }

    #[DataProvider('resolveForFiltersDataProvider')]
    public function testResolveForFilters(StringSet $speciesNames, StringSet $expected): void
    {
        $subject = new CreatorSpeciesResolver($this->getResolveDoesSpecies());

        $result = $subject->resolveForFilters($speciesNames);

        self::assertEqualsCanonicalizing($expected->getValuesArray(), $result->getValuesArray());
    }
}
