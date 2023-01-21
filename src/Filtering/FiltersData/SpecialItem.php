<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Filtering\FiltersData\Builder\MutableSpecialItem;

readonly class SpecialItem
{
    public int $count;
    public string $value;
    public string $label;
    public string $faIcon;

    public function __construct(MutableSpecialItem $source)
    {
        $this->count = $source->getCount();
        $this->value = $source->value;
        $this->label = $source->label;
        $this->faIcon = $source->faIcon;
    }
}
