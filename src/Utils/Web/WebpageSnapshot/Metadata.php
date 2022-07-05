<?php

declare(strict_types=1);

namespace App\Utils\Web\WebpageSnapshot;

use DateTimeImmutable;

class Metadata
{
    /**
     * @param string[][] $headers
     * @param string[]   $errors
     */
    public function __construct(
        public readonly string $url,
        public readonly string $ownerName,
        public readonly DateTimeImmutable $retrievedAt,
        public readonly int $httpCode,
        public readonly array $headers,
        public readonly int $childCount,
        public readonly array $errors = [],
    ) {
    }

    public static function from(Snapshot $snapshot): self
    {
        return new self(
            $snapshot->url,
            $snapshot->ownerName,
            $snapshot->retrievedAt,
            $snapshot->httpCode,
            $snapshot->headers,
            count($snapshot->getChildren()),
            $snapshot->errors,
        );
    }
}
