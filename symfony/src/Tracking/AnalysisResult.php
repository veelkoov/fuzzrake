<?php

declare(strict_types=1);

namespace App\Tracking;

use Veelkoov\Debris\StringList;

readonly class AnalysisResult
{
    public function __construct(
        public StringList $openFor,
        public StringList $closedFor,
        public bool $hasEncounteredIssues,
    ) {
    }
}
