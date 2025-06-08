<?php

declare(strict_types=1);

namespace App\Utils\Web;

use Override;

readonly class UrlForTracking implements Url
{
    public function __construct(
        public Url $original,
        private string $url,
    ) {
    }

    #[Override]
    public function getUrl(): string
    {
        return $this->url;
    }

    #[Override]
    public function recordSuccessfulFetch(): void
    {
        $this->original->recordSuccessfulFetch();
    }

    #[Override]
    public function recordFailedFetch(int $code, string $reason): void
    {
        $this->original->recordFailedFetch($code, $reason);
    }
}
