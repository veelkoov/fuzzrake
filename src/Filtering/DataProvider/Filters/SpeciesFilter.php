<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters;

use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Psl\Iter;

class SpeciesFilter implements FilterInterface
{
    /**
     * @param list<string> $wantedItems
     */
    public function __construct(
        private readonly array $wantedItems,
        private readonly SpeciesSearchResolver $resolver,
    ) {
        // TODO: Unknown, Other
    }

    public function matches(Artisan $artisan): bool
    {
        if ([] === $this->wantedItems) {
            return false;
        }

        $resolvedDoes = $this->resolver->resolveDoes($artisan->getSpeciesDoes(), $artisan->getSpeciesDoesnt());

        return Iter\any($this->wantedItems, fn (string $specie) => Iter\contains($resolvedDoes, $specie));
    }
}
