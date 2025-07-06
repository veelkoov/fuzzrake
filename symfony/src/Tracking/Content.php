<?php

declare(strict_types=1);

namespace App\Tracking;

use Veelkoov\Debris\StringList;

readonly class Content
{
    public function __construct(
        public string $content,
        public StringList $aliases,
    ) {
    }

    public function with(string $content): self
    {
        return new self($content, $this->aliases);
    }
}
