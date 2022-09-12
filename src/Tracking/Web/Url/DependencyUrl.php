<?php

declare(strict_types=1);

namespace App\Tracking\Web\Url;

use Stringable;

class DependencyUrl implements Fetchable, Stringable
{
    public function __construct(
        private readonly string $url,
        private readonly Fetchable $parent,
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

    /** @noinspection PhpRedundantMethodOverrideInspection False-positive */
    public function getOwnerName(): string
    {
        return $this->parent->getOwnerName();
    }

    /** @noinspection PhpRedundantMethodOverrideInspection False-positive */
    public function recordSuccessfulFetch(): void
    {
        $this->parent->recordSuccessfulFetch(); // TODO: What if one child succeeds, and the other one fails?
    }

    /** @noinspection PhpRedundantMethodOverrideInspection False-positive */
    public function recordFailedFetch(int $code, string $reason): void
    {
        $this->parent->recordFailedFetch($code, $reason); // TODO: What if one child fails, and the other one succeeds?
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
