<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshots;

use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

readonly class SnapshotMetadata
{
    /**
     * @param array<string, list<string>> $headers
     * @param list<string>                $errors
     */
    public function __construct(
        public string $url,
        #[Context(context: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d\TH:i:s.uP'])]
        public DateTimeImmutable $retrievedAtUtc,
        public int $httpCode,
        public array $headers,
        public array $errors,
    ) {
    }
}
