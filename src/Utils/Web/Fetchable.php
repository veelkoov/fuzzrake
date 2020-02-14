<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Entity\Artisan;

interface Fetchable
{
    public function getUrl(): string;

    public function isDependency(): bool;

    public function getArtisan(): Artisan;

    public function recordSuccessfulFetch(): void;

    public function recordFailedFetch(int $code, string $reason): void;
}
