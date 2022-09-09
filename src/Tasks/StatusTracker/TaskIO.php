<?php

declare(strict_types=1);

namespace App\Tasks\StatusTracker;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\FieldsList;
use App\Entity\EventFactory;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tracker\OfferStatusParser;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Data\FixerDifferValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskIO
{
    private readonly FieldsList $eventCreatingFields;
    private readonly FieldsList $skipDiffForFields;
    private readonly StatusTrackerTask $task;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly ArtisanRepository $repository,
        private readonly OfferStatusParser $parser,
        private readonly WebpageSnapshotManager $snapshots,
        private readonly FixerDifferValidator $fdv,
        private readonly bool $refetch,
        private readonly bool $commit,
        private readonly SymfonyStyle $io,
    ) {
        $this->task = new StatusTrackerTask($this->repository, $this->logger, $this->snapshots, $this->parser);
        $this->eventCreatingFields = new FieldsList([Field::OPEN_FOR]);
        $this->skipDiffForFields = new FieldsList([Field::CS_LAST_CHECK]);
    }

    public function performUpdates(): void
    {
        $this->snapshots->prefetchUrls($this->task->getUrlsToPrefetch(), $this->refetch, $this->io);

        $updates = $this->task->getUpdates();

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
            $this->fdv->perform($update, FixerDifferValidator::SHOW_DIFF, $this->skipDiffForFields);
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
                $event = EventFactory::forStatusTracker($update);
                $this->entityManager->persist($event);
            }

            $update->apply();
            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
    }
}
