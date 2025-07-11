<?php

declare(strict_types=1);

namespace App\Utils\Mx;

use App\Data\Definitions\Fields\Field;

final readonly class GroupedUrl
{
    public function __construct(
        public Field $type,
        public int $index,
        public string $url,
    ) {
    }

    public function getLabel(): string
    {
        $name = substr($this->type->value, 4); // 'URL_' = 4 characters

        if ($this->type->isList()) {
            $name .= ' '.($this->index + 1);
        }

        return "$name: $this->url";
    }

    public function getId(): string
    {
        return "{$this->type->value}_{$this->index}";
    }
}
