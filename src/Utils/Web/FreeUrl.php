<?php

declare(strict_types=1);

namespace Utils\Web;

use App\Utils\Web\Fetchable;

class FreeUrl implements Fetchable
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
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
}
