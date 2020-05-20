<?php

declare(strict_types=1);

namespace App\Utils\Data\Definitions;

use TRegx\CleanRegex\Match\Details\Match;

class Species
{
    private const FLAG_PREFIX_REGEXP = '^(?<flags>[a-z]{1,2})_(?<specie>.+)$';

    private const FLAG_IGNORE_THIS_FLAG = 'i';

    /**
     * @return string[]
     */
    private array $validChoicesList = [];

    /**
     * @return string[]|array[]
     */
    private array $filterChoicesTree = [];

    /**
     * @return string[]
     */
    private array $replacements;

    /**
     * @return string[]
     */
    private array $unsplittable;

    /**
     * @param array[]|string[] $species_definitions
     */
    public function __construct(array $species_definitions)
    {
        $this->buildStructures($species_definitions['valid_choices'], $this->validChoicesList,
            $this->filterChoicesTree);

        $this->replacements = $species_definitions['replacements'];
        $this->unsplittable = $species_definitions['leave_unchanged'];
    }

    /**
     * @return string[]
     */
    public function getValidChoicesList()
    {
        return $this->validChoicesList;
    }

    /**
     * @return array[]|string[]
     */
    public function getFilterChoicesTree(): array
    {
        return $this->filterChoicesTree;
    }

    /**
     * @return string[]
     */
    public function getListFixerReplacements(): array
    {
        return $this->replacements;
    }

    /**
     * @return string[]
     */
    public function getListFixerUnsplittable(): array
    {
        return $this->unsplittable;
    }

    /**
     * @param array[]|string[] $species
     */
    private function buildStructures(array $species, array &$validChoicesList, array &$filterChoicesTree): void
    {
        foreach ($species as $specie => $subspecies) {
            list($flags, $specie) = $this->splitSpecieFlagsName($specie);

            if (!$this->flagged($flags, self::FLAG_IGNORE_THIS_FLAG)) {
                $validChoicesList[] = $specie;
                $filterChoicesTree[$specie] = [];
                $dummy = null;
            } else {
                $dummy = [];
            }

            if (is_array($subspecies)) {
                if (null === $dummy) {
                    $this->buildStructures($subspecies, $validChoicesList, $filterChoicesTree[$specie]);
                } else {
                    $this->buildStructures($subspecies, $validChoicesList, $dummy);
                }
            }
        }
    }

    private function splitSpecieFlagsName(string $specie): array
    {
        return pattern(self::FLAG_PREFIX_REGEXP)->match($specie)->findFirst(function (Match $match): array {
            return [$match->group('flags')->text(), $match->group('specie')->text()];
        })->orReturn(['', $specie]);
    }

    private function flagged(string $flags, string $flag): bool
    {
        return false !== strpos($flags, $flag);
    }
}
