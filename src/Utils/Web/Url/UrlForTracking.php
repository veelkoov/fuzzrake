<?php

declare(strict_types=1);

namespace App\Utils\Web\Url;

use Override;

readonly class UrlForTracking extends AbstractBaseUrl
{
    public function __construct(
        string $url,
        public Url $original,
    ) {
        parent::__construct($url);
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

    #[Override]
    public function getCreatorId(): string
    {
        return $this->original->getCreatorId();
    }
}
