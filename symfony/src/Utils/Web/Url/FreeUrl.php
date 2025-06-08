<?php

declare(strict_types=1);

namespace App\Utils\Web\Url;

readonly class FreeUrl extends AbstractUrl
{
    public function recordSuccessfulFetch(): void
    {
    }

    public function recordFailedFetch(int $code, string $reason): void
    {
    }
}
