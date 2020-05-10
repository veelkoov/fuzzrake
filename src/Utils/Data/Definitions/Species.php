<?php

declare(strict_types=1);

namespace App\Utils\Data\Definitions;

use TRegx\CleanRegex\Match\Details\Match;

class Species
{
    private const META_INFO_PREFIX_REGEXP = '^(?<flags>[a-z]{1,2})_(?<specie>.+)$';

    private const FLAG_IGNORE_THIS_FLAG = 'i';
    private const FLAG_SKIP_THIS_AND_CHILD_IN_FILTERS = 'f'; // TODO

    /**
     * @return string[]
     */
    private array $validChoicesList;

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
        $this->validChoicesList = $this->gatherValidChoices($species_definitions['valid_choices']);
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
     *
     * @return string[]
     */
    private function gatherValidChoices(array $species): array
    {
        $result = [];

        foreach ($species as $specie => $subspecies) {
            list($flags, $specie) = $this->splitSpecieFlagsName($specie);

            if (!$this->flagged($flags, self::FLAG_IGNORE_THIS_FLAG)) {
                $result[] = $specie;
            }

            if (is_array($subspecies)) {
                $result = array_merge($result, $this->gatherValidChoices($subspecies));
            }
        }

        return $result;
    }

    private function splitSpecieFlagsName(string $specie): array
    {
        return pattern(self::META_INFO_PREFIX_REGEXP)->match($specie)->findFirst(function (Match $match): array {
            return [$match->group('flags')->text(), $match->group('specie')->text()];
        })->orReturn(['', $specie]);
    }

    private function flagged(string $flags, string $flag): bool
    {
        return false !== strpos($flags, $flag);
    }
}
