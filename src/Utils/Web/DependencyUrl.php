<?php

declare(strict_types=1);

namespace App\Utils\Web;

use Stringable;

class DependencyUrl implements Fetchable, Stringable
{
    public function __construct(
        private string $url,
        private Fetchable $parent,
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

    public function getOwnerName(): string
    {
        return $this->parent->getOwnerName();
    }

    public function recordSuccessfulFetch(): void
    {
        $this->parent->recordSuccessfulFetch(); // TODO: What if one child succeeds, and the other one fails?
    }

    public function recordFailedFetch(int $code, string $reason): void
    {
        $this->parent->recordFailedFetch($code, $reason); // TODO: What if one child fails, and the other one succeeds?
    }

    public function __toString(): string
    {
        return self::class.":{$this->url}";
    }

    public function resetFetchResults(): void
    {
        // Noop
    }
}
