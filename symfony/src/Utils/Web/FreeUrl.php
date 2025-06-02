<?php

declare(strict_types=1);

namespace App\Utils\Web;

readonly class FreeUrl implements Url
{
    public function __construct(
        public string $url,
    ) {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function recordSuccessfulFetch(): void
    {
    }

    public function recordFailedFetch(int $code, string $reason): void
    {
    }
}
