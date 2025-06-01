<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshots;

readonly class Snapshot
{
    public function __construct(
        public string $contents,
        public SnapshotMetadata $metadata,
    ) {
    }
}
