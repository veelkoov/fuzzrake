<?php

declare(strict_types=1);

namespace App\Tracking\Data;

use Veelkoov\Debris\Vecs\StringVec;

readonly class AnalysisResults
{
    public function __construct(
        public StringVec $openFor,
        public StringVec $closedFor,
        public bool $hasEncounteredIssues,
    ) {
    }

    public function anySuccess(): bool
    {
        return $this->openFor->isNotEmpty() || $this->closedFor->isNotEmpty();
    }
}
