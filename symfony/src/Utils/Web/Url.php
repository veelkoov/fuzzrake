<?php

declare(strict_types=1);

namespace App\Utils\Web;

interface Url
{
    public function getUrl(): string;

    public function recordSuccessfulFetch(): void;

    public function recordFailedFetch(int $code, string $reason): void;
}
