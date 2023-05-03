<?php

declare(strict_types=1);

namespace App\Tracking\Web\Url;

use Stringable;

class CoercedUrl implements Fetchable, Stringable
{
    public function __construct(
        private readonly Fetchable $coerced,
        public readonly string $url,
    ) {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isDependency(): bool
    {
        return $this->coerced->isDependency();
    }

    public function getOwnerName(): string
    {
        return $this->coerced->getOwnerName();
    }

    public function recordSuccessfulFetch(): void
    {
        $this->coerced->recordSuccessfulFetch();
    }

    public function recordFailedFetch(int $code, string $reason): void
    {
        $this->coerced->recordFailedFetch($code, $reason);
    }

    public function resetFetchResults(): void
    {
        $this->coerced->resetFetchResults();
    }

    public function __toString(): string
    {
        return self::class.":$this->url ({$this->coerced})";
    }
}
