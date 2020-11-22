<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates\Commissions;

use App\Entity\Artisan;
use App\Entity\Event;
use App\Tasks\TrackerUpdates\AnalysisResultInterface;
use App\Utils\StrContext\StrContextInterface;
use App\Utils\Tracking\MatchInterface;
use App\Utils\Tracking\Status;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CommissionsAnalysisResult implements AnalysisResultInterface
{
    private Artisan $artisan;
    private MatchInterface $openMatch;
    private MatchInterface $closedMatch;

    public function __construct(Artisan $artisan, MatchInterface $openMatch, MatchInterface $closedMatch)
    {
        $this->artisan = $artisan;
        $this->openMatch = $openMatch;
        $this->closedMatch = $closedMatch;
    }

    public function report(SymfonyStyle $io): void
    {
        if ($this->artisan->getVolatileData()->getStatus() !== $this->getStatus()) {
            $oldStatusText = Status::text($this->artisan->getVolatileData()->getStatus());
            $newStatusText = Status::text($this->getStatus());

            $io->caution("{$this->artisan->getName()} ( {$this->artisan->getCommissionsUrl()} ): {$this->explanation()}, $oldStatusText ---> $newStatusText");
        } elseif ($this->hasFailed()) {
            $io->note("{$this->artisan->getName()} ( {$this->artisan->getCommissionsUrl()} ): {$this->explanation()}");
        } else {
            return;
        }

        if ($this->openMatched()) {
            $io->text("Matched OPEN ({$this->getOpenRegexpId()}): ".
                "<context>{$this->getOpenStrContext()->getBefore()}</>".
                "<open>{$this->getOpenStrContext()->getSubject()}</>".
                "<context>{$this->getOpenStrContext()->getAfter()}</>");
        }

        if ($this->closedMatched()) {
            $io->text("Matched CLOSED ({$this->getClosedRegexpId()}): ".
                "<context>{$this->getClosedStrContext()->getBefore()}</>".
                "<closed>{$this->getClosedStrContext()->getSubject()}</>".
                "<context>{$this->getClosedStrContext()->getAfter()}</>");
        }
    }

    /**
     * @return object[]
     */
    public function getNewEntities(): array
    {
        if ($this->artisan->getVolatileData()->getStatus() !== $this->getStatus()) {
            return [
                $this->artisan->getVolatileData(), // Could have been just created
                new Event($this->artisan->getCommissionsUrl(), $this->artisan->getName(),
                $this->artisan->getVolatileData()->getStatus(), $this),
            ];
        } else {
            return [];
        }
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

    public function getOpenStrContext(): StrContextInterface
    {
        return $this->openMatch->getStrContext();
    }

    public function getClosedStrContext(): StrContextInterface
    {
        return $this->closedMatch->getStrContext();
    }

    private function hasFailed(): bool
    {
        return $this->bothMatched() || $this->noneMatched();
    }

    private function explanation(): string
    {
        if ($this->bothMatched()) {
            return 'both matches';
        } elseif ($this->noneMatched()) {
            return 'none matches';
        } else {
            return 'OK';
        }
    }

    private function bothMatched(): bool
    {
        return $this->openMatch->matched() && $this->closedMatch->matched();
    }

    private function noneMatched(): bool
    {
        return !$this->openMatch->matched() && !$this->closedMatch->matched();
    }

    private function openMatched(): bool
    {
        return $this->openMatch->matched();
    }

    private function closedMatched(): bool
    {
        return $this->closedMatch->matched();
    }

    private function getOpenRegexpId(): string
    {
        return $this->openMatch->getRegexp()->getId();
    }

    private function getClosedRegexpId(): string
    {
        return $this->closedMatch->getRegexp()->getId();
    }
}
