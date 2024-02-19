<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use InvalidArgumentException;

use function Psl\Iter\contains;
use function Psl\Vec\filter;

class SpecialItemsExtractor
{
    /**
     * @var array<string, bool>
     */
    private array $special = [];

    /**
     * @var list<string>
     */
    private array $common = [];

    /**
     * @param list<string> $items
     */
    public function __construct(array $items, string ...$allowedSpecialItems)
    {
        foreach ($allowedSpecialItems as $specialItem) {
            $this->special[$specialItem] = contains($items, $specialItem);
        }

        $this->common = filter($items, fn (string $item) => !contains($allowedSpecialItems, $item));
    }

    /**
     * @return list<string>
     */
    public function getCommon(): array
    {
        return $this->common;
    }

    public function hasSpecial(string $item): bool
    {
        return $this->special[$item] ?? throw new InvalidArgumentException("Special choice '$item' was not declared");
    }
}
