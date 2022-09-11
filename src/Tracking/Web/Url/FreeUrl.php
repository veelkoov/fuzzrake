<?php

declare(strict_types=1);

namespace App\Tracking\Web\Url;

use Stringable;

class FreeUrl implements Fetchable, Stringable
{
    public function __construct(
        private readonly string $url,
    ) {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isDependency(): bool
    {
        return true;
    }

    public function recordSuccessfulFetch(): void
    {
        // Nothing to do
    }

    public function recordFailedFetch(int $code, string $reason): void
    {
        // Nothing to do
    }

    public function getOwnerName(): string
    {
        return 'N/A';
    }

    public function __toString(): string
    {
        return self::class.":$this->url";
    }

    public function resetFetchResults(): void
    {
        // Noop
    }
}
