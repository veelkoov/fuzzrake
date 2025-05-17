<?php

declare(strict_types=1);

namespace App\Utils\Mx;

use App\Data\Definitions\Fields\Field;
use Psl\Str;

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
        $name = Str\strip_prefix($this->type->value, 'URL_');

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
