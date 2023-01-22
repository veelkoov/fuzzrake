<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Filtering\FiltersData\Builder\MutableItem;
use App\Filtering\FiltersData\Builder\MutableSet;

readonly class Item
{
    public string $label;
    /**
     * @var string|list<Item>
     */
    public string|array $value;
    public ?int $count; // TODO: #76 Species count, should not be nullable

    public function __construct(MutableItem $source)
    {
        $this->value = $source->value instanceof MutableSet ? $source->value->getReadonlyList() : $source->value;
        $this->label = $source->label;
        $this->count = $source->getCount();
    }
}
