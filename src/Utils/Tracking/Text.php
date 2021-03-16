<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

class Text
{
    public function __construct(
        private string $original,
        private string $cleaned,
        private string $optimized,
    ) {
    }

    public function get(): string
    {
        return $this->optimized;
    }

    public function getOriginal(): string
    {
        return $this->original;
    }

    public function getCleaned(): string
    {
        return $this->cleaned;
    }
}
