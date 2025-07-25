<?php

declare(strict_types=1);

namespace App\Utils\Web\Url;

use App\Utils\Web\UrlStrategy\Strategy;

interface Url
{
    public function getUrl(): string;

    public function getCreatorId(): string;

    public function getHost(): string;

    public function getOriginalUrl(): string;

    public function getStrategy(): Strategy;

    public function recordSuccessfulFetch(): void;

    public function recordFailedFetch(int $code, string $reason): void;
}
