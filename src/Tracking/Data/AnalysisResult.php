<?php

declare(strict_types=1);

namespace App\Tracking\Data;

use App\Utils\StrUtils;
use Override;
use Stringable;
use Veelkoov\Debris\Lists\StringList;

readonly class AnalysisResult implements Stringable
{
    public function __construct(
        public string $url,
        public StringList $openFor,
        public StringList $closedFor,
        public bool $hasEncounteredIssues,
    ) {
    }

    public function with(
        ?StringList $openFor = null,
        ?StringList $closedFor = null,
        ?bool $hasEncounteredIssues = null,
    ): self {
        return new self(
            $this->url,
            $openFor ?? $this->openFor,
            $closedFor ?? $this->closedFor,
            $hasEncounteredIssues ?? $this->hasEncounteredIssues,
        );
    }

    #[Override]
    public function __toString(): string
    {
        return "U: '$this->url'; O: {$this->openFor->join(',')}; C: {$this->closedFor->join(',')}; I: "
            .StrUtils::asStr($this->hasEncounteredIssues);
    }
}
