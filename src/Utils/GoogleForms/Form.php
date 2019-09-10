<?php

declare(strict_types=1);

namespace App\Utils\GoogleForms;

class Form
{
    /**
     * @var Item[]
     */
    private $items;

    public function __construct(array $data)
    {
        foreach ($data[1][1] as $index => $itemData) {
            $this->items[] = new Item($itemData, $index);
        }
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
