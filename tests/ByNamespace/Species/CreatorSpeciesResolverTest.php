<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Species;

use App\Species\CreatorSpeciesResolver;
use App\Species\Hierarchy\MutableSpecies;
use App\Species\Hierarchy\Specie;
use App\Species\Hierarchy\Species;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Veelkoov\Debris\Sets\StringSet;
use Veelkoov\Debris\Vecs\StringVec;

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

        $result = $subject->resolveDoes(new StringVec(), new StringVec());
        self::assertCount(0, $result);
    }

    public function testEmptyDoesAndUnknownDoesntReturnMostSpeciesOnly(): void
    {
        $subject = new CreatorSpeciesResolver($this->getBasicSpecies());

        $result = $subject->resolveDoes(new StringVec(), StringVec::of('Some unusual specie'));
        self::assertEqualsCanonicalizing(['Most species'], $result->getValuesArray());
    }

    public function testHiddenSpeciesAreNotReturned(): void
    {
        $subject = new CreatorSpeciesResolver($this->getBasicSpecies());

        $result = $subject->resolveDoes(StringVec::of('Most species'), new StringVec());

        self::assertTrue($result->contains('Most species'));
        self::assertFalse($result->contains('Hidden'));
    }

    public function testOtherSpeciesAreSimplified(): void
    {
        $subject = new CreatorSpeciesResolver($this->getBasicSpecies());

        $result = $subject->resolveDoes(StringVec::of('Some weird specie'), new StringVec());

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

    /**
     * @return list<array{StringVec, StringVec, string}>
     */
    public static function getOrderedDoesDoesntSpeciesDataProvider(): array
    {
        return [
            [StringVec::of('A', 'C'), StringVec::of('B', 'D'), '+A -B +C -D'],
            [StringVec::of('C', 'A'), StringVec::of('D', 'B'), '+A -B +C -D'],
            [StringVec::of('B', 'D'), StringVec::of('A', 'C'), '-A +B -C +D'],
            [StringVec::of('D', 'B'), StringVec::of('C', 'A'), '-A +B -C +D'],
        ];
    }

    #[DataProvider('getOrderedDoesDoesntSpeciesDataProvider')]
    public function testGetOrderedDoesDoesnt(StringVec $does, StringVec $doesnt, string $expected): void
    {
        $subject = new CreatorSpeciesResolver($this->getGetOrderedDoesDoesntSpecies());

        $result = $subject->getOrderedDoesDoesnt($does, $doesnt);
        $strResult = StringVec::mapFrom($result,
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

    /**
     * @return list<array{StringVec, StringVec, StringVec}>
     */
    public static function resolveDoesDataProvider(): array
    {
        return [
            [new StringVec(),                       new StringVec(),                          new StringVec()],
            [StringVec::of('Mammals', 'Corgis'),    StringVec::of('Canines', 'With antlers'), StringVec::of('Mammals', 'Corgis')],
            [StringVec::of('Mammals'),              StringVec::of('With antlers', 'Dogs'),    StringVec::of('Mammals', 'Canines', 'Wolves')],
            [StringVec::of('Mammals', 'Deers'),     StringVec::of('With antlers', 'Dogs'),    StringVec::of('Mammals', 'Canines', 'Wolves', 'Deers')],
            [StringVec::of('Dogs', 'With antlers'), StringVec::of(''),                        StringVec::of('With antlers', 'Deers', 'Dogs', 'Corgis', 'Dalmatians')],
            [StringVec::of('Dogs', 'With antlers'), StringVec::of('Deers'),                   StringVec::of('With antlers', 'Dogs', 'Corgis', 'Dalmatians')],
            [StringVec::of('Dogs', 'Pancakes'),     StringVec::of(''),                        StringVec::of('Other', 'Dogs', 'Corgis', 'Dalmatians')],
            [StringVec::of('Dogs', 'Other'),        StringVec::of('Dalmatians'),              StringVec::of('Other', 'Dogs', 'Corgis')],
        ];
    }

    #[DataProvider('resolveDoesDataProvider')]
    public function testResolveDoes(StringVec $does, StringVec $doesnt, StringVec $expected): void
    {
        $subject = new CreatorSpeciesResolver($this->getResolveDoesSpecies());

        $result = $subject->resolveDoes($does, $doesnt);

        self::assertEqualsCanonicalizing($expected->getValuesArray(), $result->getValuesArray());
    }

    /**
     * @return list<array{StringSet, StringSet}>
     */
    public static function resolveForFiltersDataProvider(): array
    {
        return [
            [new StringSet(), new StringSet()],
            [StringSet::of('Deers'),        StringSet::of('Deers', 'With antlers', 'Most species', 'Mammals')],
            [StringSet::of('With antlers'), StringSet::of('With antlers', 'Most species')],
            [StringSet::of('Other'),        StringSet::of('Other')],
        ];
    }

    #[DataProvider('resolveForFiltersDataProvider')]
    public function testResolveForFilters(StringSet $speciesNames, StringSet $expected): void
    {
        $subject = new CreatorSpeciesResolver($this->getResolveDoesSpecies());

        $result = $subject->resolveForFilters($speciesNames);

        self::assertEqualsCanonicalizing($expected->getValuesArray(), $result->getValuesArray());
    }
}
