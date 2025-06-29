<?php

declare(strict_types=1);

namespace App\Tracking;

use Veelkoov\Debris\StringList;

readonly class AnalysisResults
{
    public function __construct(
        public StringList $openFor,
        public StringList $closedFor,
        public bool $hasEncounteredIssues,
    ) {
    }

    public function anySuccess(): bool
    {
        return $this->openFor->isNotEmpty() || $this->closedFor->isNotEmpty();
    }
}
