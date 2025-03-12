<?php

declare(strict_types=1);

namespace App\Species;

use App\Utils\Collections\StringList;
use Veelkoov\Debris\Base\DMap;
use Veelkoov\Debris\Base\Internal\Pair;
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

        foreach ($ordered as $specie => $does) {
            $descendants = $this->getVisibleSelfAndDescendants($specie);

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
     * @return DMap<Specie, bool> Specie => Does?
     */
    public function getOrderedDoesDoesnt(StringList $speciesDoes, StringList $speciesDoesnt): DMap
    {
        /** @var DMap<Specie, bool> $result */
        $result = new DMap();

        foreach ($speciesDoes as $specieDone) {
            foreach ($this->getVisibleSpecieOrParentOrOtherForUnusual($specieDone) as $specie) {
                $result->set($specie, true);
            }
        }

        foreach ($speciesDoesnt as $specieNotDone) {
            foreach ($this->getVisibleSpecieOrEmptySetForUnusual($specieNotDone) as $specie) {
                $result->set($specie, false);
            }
        }

        return $result->sorted(function (Pair $item1, Pair $item2): int {
            $depthDiff = $item1->key->getDepth() - $item2->key->getDepth();

            if (0 !== $depthDiff) {
                return $depthDiff;
            } elseif ($item2->value) {
                return 1;
            } else {
                return 0 - ($item1->value ? 1 : 0);
            }
        });
    }

    private function getVisibleSelfAndDescendants(Specie $specie): StringList
    {
        return $this->selfAndDescendantsCache[$specie->getName()] ??= $specie->getThisAndDescendants()
            ->filter(static fn (Specie $specie) => !$specie->getHidden())->getNames();
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
