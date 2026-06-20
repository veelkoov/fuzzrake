<?php

declare(strict_types=1);

namespace App\Tracking\Data;

use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Url\Url;
use Veelkoov\Debris\Vecs\StringVec;

class AnalysisInput
{
    public string $contents {
        get => $this->snapshot->contents;
    }

    public StringVec $creatorAliases {
        get => new StringVec([$this->creator->getName(), ...$this->creator->getFormerly()]);
    }

    public function __construct(
        public readonly Url $url,
        public readonly Snapshot $snapshot,
        public readonly Creator $creator,
    ) {
    }
}
