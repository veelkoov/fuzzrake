<?php

declare(strict_types=1);

namespace App\Data\Species;

use App\Utils\StringList;
use Psl\Vec;

class CreatorSpeciesResolver
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

        /**
         * @var Specie $specie
         * @var bool   $does
         */
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
            ...Vec\map($speciesDoes, fn (string $specieName) => [$this->getSpecieOrOtherForUnusual($specieName), true]),
            ...Vec\map($speciesDoesnt, fn (string $specieName) => [$this->getSpecieOrOtherForUnusual($specieName), false]),
        ];

        // Remove any "doesn't do" "Other"
        $result = Vec\filter($result, fn (array $specie) => $this->other !== $specie[0] || true === $specie[1]);

        usort($result, function (array $pair1, array $pair2) {
            $depthDiff = (int) ($pair1[0]->depth - $pair2[0]->depth); // Redundant cast to (int) for PHPStan

            return 0 !== $depthDiff ? $depthDiff : (int) $pair2[1] - (int) $pair1[1];
        });

        return $result;
    }

    /**
     * @return list<string>
     */
    private function getSelfAndDescendants(Specie $self): array
    {
        return $this->selfAndDescendantsCache[$self->name]
            ??= Vec\map($self->getSelfAndDescendants(), fn (Specie $specie) => $specie->name);
    }

    private function getSpecieOrOtherForUnusual(string $specieName): Specie
    {
        if ($this->species->hasName($specieName)) {
            return $this->species->getByName($specieName);
        } else {
            return $this->other;
        }
    }
}
