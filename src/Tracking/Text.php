<?php

declare(strict_types=1);

namespace App\Tracking;

class Text
{
    private string $unused;

    public function __construct(
        private readonly string $original,
        private readonly string $cleaned,
    ) {
        $this->unused = $this->cleaned;
    }

    public function getOriginal(): string
    {
        return $this->original;
    }

    public function getCleaned(): string
    {
        return $this->cleaned;
    }

    public function getUnused(): string
    {
        return $this->unused;
    }

    public function use(int $firstByte, int $lastByte): void
    {
        for ($i = $firstByte; $i <= $lastByte; ++$i) {
            $this->unused[$i] = ' ';
        }
    }
}
