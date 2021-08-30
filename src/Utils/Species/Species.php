<?php

declare(strict_types=1);

namespace App\Utils\Species;

use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
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
    private array $flat;

    /**
     * @var Specie[] Species fit for filtering
     */
    private array $tree;

    /**
     * @var string[] Names of species considered valid by the validator (list of all, not only fit for filtering)
     */
    private array $validNames;

    public function __construct(
        array $speciesDefinitions,
        private ArtisanRepository $artisanRepository,
    ) {
        $this->replacements = new Replacements($speciesDefinitions['replacements'], 'i', $speciesDefinitions['commonRegexPrefix'], $speciesDefinitions['commonRegexSuffix']);
        $this->unsplittable = $speciesDefinitions['leave_unchanged'];

        $builder = new HierarchyAwareBuilder($speciesDefinitions['valid_choices']);
        $this->flat = $builder->getFlat();
        $this->tree = $builder->getTree();
        $this->validNames = $builder->getValidNames();
    }

    /**
     * @return string[]
     */
    public function getValidNames(): array
    {
        return $this->validNames;
    }

    /**
     * @return Specie[]
     */
    public function getFlat(): array
    {
        return $this->flat;
    }

    /**
     * @return Specie[]
     */
    public function getTree(): array
    {
        return $this->tree;
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

    /**
     * @return SpecieStats[]
     */
    public function getStats(): array
    {
        return (new StatsCalculator(Artisan::wrapAll($this->artisanRepository->getActive()), $this->flat))->get();
    }
}
