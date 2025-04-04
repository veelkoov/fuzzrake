<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Utils\Collections\StringList;
use InvalidArgumentException;

class SpecialItemsExtractor
{
    /**
     * @var array<string, bool>
     */
    private array $special = [];

    public readonly StringList $common;

    public function __construct(StringList $items, string ...$allowedSpecialItems)
    {
        foreach ($allowedSpecialItems as $specialItem) {
            $this->special[$specialItem] = $items->contains($specialItem);
        }

        $this->common = $items->minusAll($allowedSpecialItems)->freeze();
    }

    public function hasSpecial(string $item): bool
    {
        return $this->special[$item] ?? throw new InvalidArgumentException("Special choice '$item' was not declared");
    }
}
