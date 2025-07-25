<?php

declare(strict_types=1);

namespace App\Utils\Web\Url;

use Override;

readonly class FreeUrl extends AbstractBaseUrl
{
    public function __construct(
        string $url,
        private string $creatorId,
    ) {
        parent::__construct($url);
    }

    public function getCreatorId(): string
    {
        return $this->creatorId;
    }

    #[Override]
    public function getOriginalUrl(): string
    {
        return $this->getUrl();
    }

    #[Override]
    public function recordSuccessfulFetch(): void
    {
    }

    #[Override]
    public function recordFailedFetch(int $code, string $reason): void
    {
    }
}
