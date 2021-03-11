<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates\Commissions;

use App\Entity\Artisan;
use App\Entity\ArtisanCommissionsStatus;
use App\Tasks\TrackerUpdates\ArtisanUpdatesInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CommissionsArtisanUpdates implements ArtisanUpdatesInterface
{
    /**
     * @var ArtisanCommissionsStatus[]
     */
    private array $acses;

    public function __construct(
        private Artisan $artisan,
    ) {
        $this->acses = [];
    }

    public function report(SymfonyStyle $io): void
    {
//        if ($this->artisan->getVolatileData()->getStatus() !== $this->getStatus()) {
//            $oldStatusText = Status::text($this->artisan->getVolatileData()->getStatus());
//            $newStatusText = Status::text($this->getStatus());
//
//            $io->caution("{$this->artisan->getName()} ( {$this->artisan->getCommissionsUrl()} ): {$this->explanation()}, $oldStatusText ---> $newStatusText");
//        } elseif ($this->hasFailed()) {
//            $io->note("{$this->artisan->getName()} ( {$this->artisan->getCommissionsUrl()} ): {$this->explanation()}");
//        } else {
//            return;
//        }
//
//        if ($this->openMatched()) {
//            $io->text("Matched OPEN ({$this->getOpenRegexpId()}): ".
//                "<context>{$this->getOpenStrContext()->getBefore()}</>".
//                "<open>{$this->getOpenStrContext()->getSubject()}</>".
//                "<context>{$this->getOpenStrContext()->getAfter()}</>");
//        }
//
//        if ($this->closedMatched()) {
//            $io->text("Matched CLOSED ({$this->getClosedRegexpId()}): ".
//                "<context>{$this->getClosedStrContext()->getBefore()}</>".
//                "<closed>{$this->getClosedStrContext()->getSubject()}</>".
//                "<context>{$this->getClosedStrContext()->getAfter()}</>");
//        }
    }

    /**
     * @return object[]
     */
    public function getCreatedEntities(): array
    {
        return [
            $this->artisan->getVolatileData(), // Could have been just created
//            new Event($this->artisan->getCommissionsUrl(), $this->artisan->getName(), null, $this), // FIXME
            ...$this->acses,
        ];
    }

    /**
     * @return object[]
     */
    public function getRemovedEntities(): array
    {
        return $this->artisan->getCommissions()->toArray();
    }

    /**
     * @param ArtisanCommissionsStatus[] $acses
     */
    public function addAcses(array $acses): void
    {
        $this->acses = array_merge($this->acses, $acses);
    }
}
