<?php

declare(strict_types=1);

namespace App\Utils\Species;

use App\Utils\Regexp\Replacements;

class Species
{
    private Replacements $replacements;

    /**
     * @return string[]
     */
    private array $unsplittable;

    /**
     * @var Specie[] Associative: key = name, value = Specie object. Species fit for filtering
     */
    private array $speciesFlat;

    /**
     * @var Specie[] Species fit for filtering
     */
    private array $speciesTree;

    /**
     * @var string[] Names of species considered valid by the validator (list of all, not only fit for filtering)
     */
    private array $validChoicesList;

    public function __construct(
        array $speciesDefinitions
    ) {
        $this->replacements = new Replacements($speciesDefinitions['replacements'], 'i', $speciesDefinitions['commonRegexPrefix'], $speciesDefinitions['commonRegexSuffix']);
        $this->unsplittable = $speciesDefinitions['leave_unchanged'];

        $builder = new HierarchyAwareBuilder($speciesDefinitions['valid_choices']);
        $this->speciesFlat = $builder->getFlat();
        $this->speciesTree = $builder->getTree();
        $this->validChoicesList = $builder->getValidNames();
    }

    /**
     * @return string[]
     */
    public function getValidChoicesList(): array
    {
        return $this->validChoicesList;
    }

    /**
     * @return Specie[]
     */
    public function getSpeciesFlat(): array
    {
        return $this->speciesFlat;
    }

    /**
     * @return Specie[]
     */
    public function getSpeciesTree(): array
    {
        return $this->speciesTree;
    }

    public function getListFixerReplacements(): Replacements
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
}
