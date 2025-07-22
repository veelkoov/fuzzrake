<?php

declare(strict_types=1);

namespace App\Tracking\Data;

use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Web\Snapshots\Snapshot;
use Veelkoov\Debris\StringList;

class AnalysisInput
{
    public string $contents {
        get => $this->snapshot->contents;
    }

    public string $url {
        get => $this->snapshot->metadata->url;
    }

    public StringList $creatorAliases {
        get => new StringList([$this->creator->getName(), ...$this->creator->getFormerly()]);
    }

    public function __construct(
        public readonly Snapshot $snapshot,
        public readonly Creator $creator,
    ) {
    }
}
