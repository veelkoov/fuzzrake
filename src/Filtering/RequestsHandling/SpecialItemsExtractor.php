<?php

declare(strict_types=1);

namespace App\Filtering\RequestsHandling;

use Veelkoov\Debris\Maps\StringToBool;
use Veelkoov\Debris\Sets\StringSet;

class SpecialItemsExtractor
{
    private StringToBool $special;
    public readonly StringSet $common;

    public function __construct(StringSet $items, string ...$allowedSpecialItems)
    {
        $this->special = StringToBool::fromKeys($allowedSpecialItems,
            static fn (string $specialItem): bool => $items->contains($specialItem))->freeze();

        $this->common = $items->minusAll($allowedSpecialItems)->freeze();
    }

    public function hasSpecial(string $item): bool
    {
        return $this->special->get($item);
    }
}
