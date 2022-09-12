<?php

declare(strict_types=1);

namespace App\Tracking\Web\Url;

interface Fetchable
{
    public function getUrl(): string;

    public function isDependency(): bool;

    public function getOwnerName(): string;

    public function recordSuccessfulFetch(): void;

    public function recordFailedFetch(int $code, string $reason): void;

    public function resetFetchResults(): void;

    public function __toString();
}
