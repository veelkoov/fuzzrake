<?php

declare(strict_types=1);

namespace App\Tracking\Data;

use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Url\Url;
use Veelkoov\Debris\Lists\StringList;

class AnalysisInput
{
    public string $contents {
        get => $this->snapshot->contents;
    }

    public StringList $creatorAliases {
        get => new StringList([$this->creator->getName(), ...$this->creator->getFormerly()]);
    }

    public function __construct(
        public readonly Url $url,
        public readonly Snapshot $snapshot,
        public readonly Creator $creator,
    ) {
    }
}
