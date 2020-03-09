<?php

declare(strict_types=1);

namespace App\Service;

class Species
{
    /**
     * @var string[]
     */
    private array $validChoices;

    /**
     * @var string[]
     */
    private array $nonsplittable;

    /**
     * @var string[]
     */
    private array $replacements;

    public function __construct(array $speciesData)
    {
        $this->validChoices = $this->gatherValidChoices($speciesData['valid_choices']);
        $this->replacements = $speciesData['replacements'];
        $this->nonsplittable = $speciesData['leave_unchanged'];
    }

    /**
     * @return string[]
     */
    public function getValidChoices(): array
    {
        return $this->validChoices;
    }

    /**
     * @param string[] $species
     */
    private function gatherValidChoices(array $species): array
    {
        $result = array_keys($species);

        foreach ($species as $specie => $subspecies) {
            if (is_array($subspecies)) {
                $result = array_merge($result, $this->gatherValidChoices($subspecies));
            }
        }

        return $result;
    }

    /**
     * @return string[]
     */
    public function getReplacements(): array
    {
        return $this->replacements;
    }

    /**
     * @return string[]
     */
    public function getNonsplittable(): array
    {
        return $this->nonsplittable;
    }
}
