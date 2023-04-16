<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Data\Species\CreatorSpeciesResolver;
use App\Data\Species\SpeciesList;
use App\Filtering\DataRequests\Consts;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Psl\Iter;
use Psl\Vec;

class SpeciesFilter implements FilterInterface
{
    private readonly bool $wantsUnknown;

    /**
     * @var list<string>
     */
    private readonly array $wantedItems;
    private readonly CreatorSpeciesResolver $resolver;

    /**
     * @param list<string> $wantedItems
     */
    public function __construct(
        array $wantedItems,
        private readonly SpeciesList $speciesList,
    ) {
        $this->resolver = new CreatorSpeciesResolver($this->speciesList);

        $this->wantsUnknown = Iter\contains($wantedItems, Consts::FILTER_VALUE_UNKNOWN);

        $wantedItems = Vec\filter($wantedItems, fn (string $item) => !Iter\contains([
            Consts::FILTER_VALUE_UNKNOWN,
        ], $item));

        foreach ($wantedItems as $specieName) {
            foreach ($this->speciesList->getByName($specieName)->getDescendants() as $subspecie) {
                $wantedItems[] = $subspecie->name;
            }
        }

        $this->wantedItems = array_unique($wantedItems);
    }

    public function matches(Artisan $artisan): bool
    {
        if (!$this->wantsUnknown && [] === $this->wantedItems) {
            return false;
        }

        if ('' === $artisan->getSpeciesDoes() && '' === $artisan->getSpeciesDoesnt()) {
            return $this->wantsUnknown;
        }

        $resolvedDoes = $this->resolver->resolveDoes($artisan->getSpeciesDoes(), $artisan->getSpeciesDoesnt());

        return Iter\any($this->wantedItems, fn (string $specie) => Iter\contains($resolvedDoes, $specie));
    }
}
