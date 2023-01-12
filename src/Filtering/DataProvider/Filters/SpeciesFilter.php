<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters;

use App\Filtering\Consts;
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

    /**
     * @param list<string> $wantedItems
     */
    public function __construct(
        array $wantedItems,
        private readonly SpeciesSearchResolver $resolver,
    ) {
        $this->wantsUnknown = Iter\contains($wantedItems, Consts::FILTER_VALUE_UNKNOWN);

        $this->wantedItems = Vec\filter($wantedItems, fn (string $item) => !Iter\contains([
            Consts::FILTER_VALUE_UNKNOWN,
        ], $item));
    }

    public function matches(Artisan $artisan): bool
    {
        if (!$this->wantsUnknown && [] === $this->wantedItems) {
            return false;
        }

        if ($this->wantsUnknown && '' === $artisan->getSpeciesDoes() && '' === $artisan->getSpeciesDoesnt()) {
            return true;
        }

        $resolvedDoes = $this->resolver->resolveDoes($artisan->getSpeciesDoes(), $artisan->getSpeciesDoesnt());

        return Iter\any($this->wantedItems, fn (string $specie) => Iter\contains($resolvedDoes, $specie));
    }
}
