<?php

declare(strict_types=1);

namespace App\Species;

use Psl\Vec;
use Psl\Iter;
use SplObjectStorage as Map;

class CreatorSpeciesResolver {
    /**
     * @var array<string, list<string>>
     */
    private array $selfAndDescendantsCache = [];

    private readonly Species $species;
    private readonly Specie $mostSpecies;
    private readonly Specie $other;

    public function __construct(
        SpeciesService $speciesService,
    )
    {
        $this->species = $speciesService->species;

        $this->mostSpecies = $this->species->getByName("Most species"); // grep-assumed-does-specie-when-artisan-has-only-doesnt
        $this->other = $this->species->getByName("Other"); // grep-species-other
    }

//    /**
//     * @param list<string> $speciesDoes
//     * @param list<string> $speciesDoesnt
//     * @return list<string>
//     */
//    public function resolveDoes(array $speciesDoes, array $speciesDoesnt): array {
//        $assumedSpeciesDoes = [] === $speciesDoes && [] !== $speciesDoesnt
//            ?[$this->mostSpecies->name] : $speciesDoes;
//
//        $ordered = $this->getOrderedDoesDoesnt($assumedSpeciesDoes, $speciesDoesnt);
//
//        $result = new Map();
//
//        foreach ($ordered as $specieName => $does) {
//            $descendants = $this->getVisibleSelfAndDescendants($this->species->getByName($specieName));
//
//            foreach ($descendants as $descendant) {
//              if ($does) {
//                  $descendants.forEach(result::add);
//              } else {
//                  $descendants.forEach(result::remove);
//              }
//        }
//
//        return $result->;
//    }

//    /**
//     * @param list<string> $speciesDoes
//     * @param list<string> $speciesDoesnt
//     * @return Map<Specie, bool> "Specie name" => Does?
//     */
//    public function getOrderedDoesDoesnt(array $speciesDoes, array $speciesDoesnt): Map
//    {
//        $knownDoes = speciesDoes.map(::getVisibleSpecieOrParentOrOtherForUnusual).flatten().toSet()
//        $knownDoesnt = speciesDoesnt.map(::getVisibleSpecieOrEmptySetForUnusual).flatten().toSet()
//
//        var result: List<Pair<Specie, Boolean>> = listOf<Pair<Specie, Boolean>>()
//            .plus(knownDoes.map { specie -> specie to true })
//            .plus(knownDoesnt.map { specie -> specie to false })
//
//        result = result.sortedWith { item1: Pair<Specie, Boolean>, item2: Pair<Specie, Boolean> ->
//            val depthDiff = item1.first.getDepth() - item2.first.getDepth()
//
//            if (0 != depthDiff) { depthDiff } else {
//                if (item2.second) 1 else 0 - if (item1.second) 1 else 0
//            }
//        }
//
//        return result.toMap() // Map<Specie, Boolean>
//    }

    /**
     * @return list<string>
     */
    private function getVisibleSelfAndDescendants(Specie $specie): array
    {
        return $this->selfAndDescendantsCache[$specie->name] ??= Vec\filter(
                Vec\map(
                    $specie->getThisAndDescendants(),
                    fn(Specie $item) => $item->getName(),
                ),
                fn(string $item) => Iter\contains($this->species->getVisibleNames(), $item),
            );
    }

    /**
     * @return list<Specie>
     */
    private function getVisibleSpecieOrParentOrOtherForUnusual(string $specieName): array
    {
        if (!$this->species->hasName($specieName)) {
            return [$this->other];
        }

        $result = [];
        $unresolved = [$this->species->getByName($specieName)];

        while ([] !== $unresolved) {
            $specie = array_shift($unresolved);

            if ($specie->getHidden()) {
                array_push($unresolved, ...$specie->getParents());
            } else {
                $result[] = $specie;
            }
        }

        if ([] === $result) {
            throw new SpecieException("$specieName is hidden and does not have a single visible parent");
        }

        return $result;
    }

    /**
     * @return list<Specie>
     */
    private function getVisibleSpecieOrEmptySetForUnusual(string $specieName): array
    {
        if (!$this->species->hasName($specieName) || $this->species->getByName($specieName)->getHidden()) {
            return [];
        }

        return [$this->species->getByName($specieName)];
    }
}
