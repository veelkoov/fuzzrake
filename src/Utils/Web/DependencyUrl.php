<?php

declare(strict_types=1);

namespace App\Utils\Web;

class DependencyUrl implements Fetchable
{
    private string $url;
    private Fetchable $parent;

    public function __construct(string $url, Fetchable $parent)
    {
        $this->url = $url;
        $this->parent = $parent;
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

    public function __toString()
    {
        return __CLASS__.":{$this->url}";
    }
}
