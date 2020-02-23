<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\StrContext\StrContextInterface;

class AnalysisResult
{
    private MatchInterface $openMatch;
    private MatchInterface $closedMatch;

    public function __construct(MatchInterface $openMatch, MatchInterface $closedMatch)
    {
        $this->openMatch = $openMatch;
        $this->closedMatch = $closedMatch;
    }

    public function getStatus(): ?bool
    {
        if ($this->openMatch->matched()) {
            if ($this->closedMatch->matched()) {
                return null;
            } else {
                return true;
            }
        } else {
            if ($this->closedMatch->matched()) {
                return false;
            } else {
                return null;
            }
        }
    }

    public function hasFailed(): bool
    {
        return $this->bothMatched() || $this->noneMatched();
    }

    public function explanation(): string
    {
        if ($this->bothMatched()) {
            return 'both matches';
        } elseif ($this->noneMatched()) {
            return 'none matches';
        } else {
            return 'OK';
        }
    }

    public function getOpenStrContext(): StrContextInterface
    {
        return $this->openMatch->getStrContext();
    }

    public function getClosedStrContext(): StrContextInterface
    {
        return $this->closedMatch->getStrContext();
    }

    public function bothMatched(): bool
    {
        return $this->openMatch->matched() && $this->closedMatch->matched();
    }

    public function noneMatched(): bool
    {
        return !$this->openMatch->matched() && !$this->closedMatch->matched();
    }

    public function openMatched(): bool
    {
        return $this->openMatch->matched();
    }

    public function closedMatched(): bool
    {
        return $this->closedMatch->matched();
    }

    public function getOpenRegexpId(): string
    {
        return $this->openMatch->getRegexp()->getId();
    }

    public function getClosedRegexpId(): string
    {
        return $this->closedMatch->getRegexp()->getId();
    }

    public static function getNull(): self
    {
        return new self(NullMatch::get(), NullMatch::get());
    }
}
