<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\FieldsList;
use App\Entity\EventFactory;
use App\Service\WebpageSnapshotManager;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Data\FixerDifferValidator;
use App\Utils\Tracking\TrackerException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TrackerTaskRunner
{
    private readonly FieldsList $eventCreatingFields;
    private readonly FieldsList $skipDiffForFields;

    /** @noinspection PhpPropertyOnlyWrittenInspection */
    public function __construct(
        private readonly TrackerTaskInterface $trackerTask,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly WebpageSnapshotManager $snapshots,
        private readonly FixerDifferValidator $fdv,
        private readonly bool $refetch,
        private readonly bool $commit,
        private readonly SymfonyStyle $io,
    ) {
        $this->eventCreatingFields = new FieldsList([Field::OPEN_FOR]);
        $this->skipDiffForFields = new FieldsList([Field::CS_LAST_CHECK]);
    }

    /**
     * @throws TrackerException
     */
    public function performUpdates()
    {
        $this->snapshots->prefetchUrls($this->trackerTask->getUrlsToPrefetch(), $this->refetch, $this->io);

        $updates = $this->trackerTask->getUpdates();

        $this->report($updates);

        if ($this->commit) {
            $this->apply($updates);
        }
    }

    /**
     * @param ArtisanChanges[] $updates
     */
    private function report(array $updates): void
    {
        foreach ($updates as $update) {
            $this->fdv->perform($update, FixerDifferValidator::SHOW_DIFF, skipDiffFor: $this->skipDiffForFields);
        }
    }

    /**
     * @param ArtisanChanges[] $updates
     */
    private function apply(array $updates): void
    {
        $this->io->progressStart(count($updates));

        foreach ($updates as $update) {
            if ($update->differs($this->eventCreatingFields)) {
                $event = EventFactory::forCsTracker($update);
                $this->entityManager->persist($event);
            }

            $update->apply();
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
    }
}
