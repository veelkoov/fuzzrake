<?php

declare(strict_types=1);

namespace App\Utils\Web\Url;

use App\Utils\Web\UrlStrategy\Strategy;
use Override;

readonly class UrlForTracking extends AbstractUrl
{
    public function __construct(
        string $url,
        public Url $original,
        ?Strategy $strategy = null,
    ) {
        parent::__construct($url, $strategy);
    }

    #[Override]
    public function getOriginalUrl(): string
    {
        return $this->original->getUrl();
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
