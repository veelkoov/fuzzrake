<?php

declare(strict_types=1);

namespace App\Species;

use App\Utils\Collections\StringList;
use Veelkoov\Debris\StringSet;

final class CreatorSpeciesResolver
{
    /**
     * @var array<string, StringList>
     */
    private array $selfAndDescendantsCache = [];

    private readonly Specie $mostSpecies;
    private readonly Specie $other;

    public function __construct(
        private readonly Species $species,
    ) {
        $this->mostSpecies = $this->species->getByName('Most species'); // grep-assumed-does-specie-when-artisan-has-only-doesnt
        $this->other = $this->species->getByName('Other'); // grep-species-other
    }

    public function resolveDoes(StringList $speciesDoes, StringList $speciesDoesnt): StringSet
    {
        $assumedSpeciesDoes = $speciesDoes->isEmpty() && $speciesDoesnt->isNotEmpty()
            ? StringList::of($this->mostSpecies->getName()) : $speciesDoes;

        $ordered = $this->getOrderedDoesDoesnt($assumedSpeciesDoes, $speciesDoesnt);

        $result = new StringSet();

        foreach ($ordered as $specieName => $does) {
            $descendants = $this->getVisibleSelfAndDescendants($this->species->getByName($specieName));

            foreach ($descendants as $descendant) {
                if ($does) {
                    $result->add($descendant);
                } else {
                    $result->remove($descendant);
                }
            }
        }

        return $result;
    }

    /**
     * @return iterable<string, bool> "Specie name" => Does?
     */
    public function getOrderedDoesDoesnt(StringList $speciesDoes, StringList $speciesDoesnt): iterable
    {
        $result = [];

        foreach ($speciesDoes as $specieDone) {
            foreach ($this->getVisibleSpecieOrParentOrOtherForUnusual($specieDone) as $specie) {
                $result[] = [$specie, true];
            }
        }

        foreach ($speciesDoesnt as $specieNotDone) {
            foreach ($this->getVisibleSpecieOrEmptySetForUnusual($specieNotDone) as $specie) {
                $result[] = [$specie, false];
            }
        }

        usort($result, function (array $item1, array $item2): int {
            $depthDiff = $item1[0]->getDepth() - $item2[0]->getDepth();

            if (0 !== $depthDiff) {
                return $depthDiff;
            } elseif ($item2[1]) {
                return 1;
            } else {
                return 0 - ($item1[1] ? 1 : 0);
            }
        });

        foreach ($result as $pair) {
            yield $pair[0]->getName() => $pair[1];
        }
    }

    private function getVisibleSelfAndDescendants(Specie $specie): StringList
    {
        return $this->selfAndDescendantsCache[$specie->getName()] ??= StringList::mapFrom(
            $specie->getThisAndDescendants()->filter(fn (Specie $specie) => !$specie->getHidden()),
            fn (Specie $specie) => $specie->getName(),
        );
    }

    private function getVisibleSpecieOrParentOrOtherForUnusual(string $specieName): SpecieSet
    {
        if (!$this->species->hasName($specieName)) {
            return SpecieSet::of($this->other);
        }

        $result = new SpecieSet();
        $unresolved = [$this->species->getByName($specieName)];

        while ([] !== $unresolved) {
            $specie = array_shift($unresolved);

            if ($specie->getHidden()) {
                array_push($unresolved, ...$specie->getParents());
            } else {
                $result->add($specie);
            }
        }

        if ($result->isEmpty()) {
            throw new SpecieException("$specieName is hidden and does not have a single visible parent");
        }

        return $result;
    }

    private function getVisibleSpecieOrEmptySetForUnusual(string $specieName): SpecieSet
    {
        if (!$this->species->hasName($specieName) || $this->species->getByName($specieName)->getHidden()) {
            return new SpecieSet();
        }

        return SpecieSet::of($this->species->getByName($specieName));
    }
}
