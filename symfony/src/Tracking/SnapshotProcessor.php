<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Utils\Web\Snapshots\Snapshot;

class SnapshotProcessor
{
    public function analyse(Snapshot $snapshot): AnalysisResult
    {
        return new AnalysisResult();
    }
}
