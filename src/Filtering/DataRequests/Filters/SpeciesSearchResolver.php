<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Utils\Species\Specie;
use App\Utils\Species\SpeciesList;
use App\Utils\StringList;
use Psl\Vec;

class SpeciesSearchResolver
{
    /**
     * @var array<string, list<string>>
     */
    private array $selfAndDescendantsCache = [];
    private readonly Specie $other;

    public function __construct(
        private readonly SpeciesList $species,
    ) {
        $this->other = $this->species->getByName('Other'); // grep-species-other
    }

    /**
     * @return list<string>
     */
    public function resolveDoes(string $speciesDoes, string $speciesDoesnt): array
    {
        if ('' === $speciesDoes && '' !== $speciesDoesnt) {
            $speciesDoes = 'Most species'; // grep-assumed-does-specie-when-artisan-has-only-doesnt
        }

        $speciesDoes = StringList::unpack($speciesDoes);
        $speciesDoesnt = StringList::unpack($speciesDoesnt);

        $ordered = $this->getOrderedDoesDoesnt($speciesDoes, $speciesDoesnt);

        /** @var array<string, boolean> Key = specie name, value = true */
        $result = [];

        /** @var Specie $specie */
        /** @var bool $does */
        foreach ($ordered as [$specie, $does]) {
            $descendants = $this->getSelfAndDescendants($specie);

            if ($does) {
                foreach ($descendants as $descendant) {
                    $result[$descendant] = true;
                }
            } else {
                foreach ($descendants as $descendant) {
                    unset($result[$descendant]);
                }
            }
        }

        return array_keys($result);
    }

    /**
     * @param list<string> $speciesDoes
     * @param list<string> $speciesDoesnt
     *
     * @return list<array{Specie, boolean}> List of pairs: Specie + "does?", ordered by 1) specie depth, and 2) "does?"
     */
    public function getOrderedDoesDoesnt(array $speciesDoes, array $speciesDoesnt): array
    {
        $result = [
            ...Vec\map($speciesDoes, fn (string $specie) => [$this->species->getByNameOrCreate($specie, false), true]),
            ...Vec\map($speciesDoesnt, fn (string $specie) => [$this->species->getByNameOrCreate($specie, false), false]),
        ];

        // Change all non-usual species a "does Other"
        $result = Vec\map($result, fn (array $specie) => $specie[0]->isRoot() && $specie[0]->isLeaf()
            ? [$this->other, true] : [$specie[0], $specie[1]]);

        usort($result, function (array $pair1, array $pair2) {
            $depthDiff = (int) ($pair1[0]->getDepth() - $pair2[0]->getDepth()); // Redundant cast to (int) for PHPStan

            return 0 !== $depthDiff ? $depthDiff : (int) $pair2[1] - (int) $pair1[1];
        });

        return $result;
    }

    /**
     * @return list<string>
     */
    private function getSelfAndDescendants(Specie $self): array
    {
        return $this->selfAndDescendantsCache[$self->getName()]
            ??= Vec\map($self->getSelfAndDescendants(), fn (Specie $specie) => $specie->getName());
    }
}
