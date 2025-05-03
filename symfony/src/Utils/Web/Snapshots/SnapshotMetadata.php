<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshots;

use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;

readonly class SnapshotMetadata
{
    /**
     * @param array<string, list<string>> $headers
     * @param list<string>                $errors
     */
    public function __construct(
        public string $url,
        public string $ownerCreatorId,
        public DateTimeImmutable $retrievedAtUtc,
        public int $httpCode,
        public array $headers,
        public array $errors,
    ) {
    }

    public static function forError(string $url, string $ownerCreatorId, string $error): self
    {
        return new self($url, $ownerCreatorId, UtcClock::now(), 0, [], [$error]);
    }
}
