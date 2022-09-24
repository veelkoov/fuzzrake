<?php

declare(strict_types=1);

namespace App\Tracking;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\FieldsList;
use App\Entity\ArtisanUrl;
use App\Entity\EventFactory;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tracking\OfferStatus\OfferStatus;
use App\Tracking\OfferStatus\OfferStatusProcessor;
use App\Tracking\OfferStatus\OfferStatusResult;
use App\Tracking\Web\WebpageSnapshot\Snapshot;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Data\FixerDifferValidator;
use App\Utils\StringList;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Psl\Dict\merge;
use function Psl\Vec\map;

class StatusTracker
{
    /**
     * @var Artisan[]
     */
    private readonly array $artisans;
    private readonly FieldsList $eventCreatingFields;
    private readonly FieldsList $skipDiffForFields;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly ArtisanRepository $repository,
        private readonly OfferStatusProcessor $processor,
        private readonly WebpageSnapshotManager $snapshots,
        private readonly FixerDifferValidator $fdv,
        private readonly bool $refetch,
        private readonly bool $commit,
        private readonly SymfonyStyle $io,
    ) {
        $this->artisans = Artisan::wrapAll($this->repository->findAll());
        $this->eventCreatingFields = new FieldsList([Field::OPEN_FOR]);
        $this->skipDiffForFields = new FieldsList([Field::CS_LAST_CHECK]);
    }

    public function performUpdates(): void
    {
        $urls = $this->getUrlsToFetch();
        $this->snapshots->prefetchUrls($urls, $this->refetch, $this->io);

        $updates = map($this->artisans, $this->getUpdatesFor(...));

        $this->report($updates);

        if ($this->commit) {
            $this->apply($updates);
        }
    }

    public function getUpdatesFor(Artisan $artisan): ArtisanChanges
    {
        $urls = $artisan->getUrlObjs(Field::URL_COMMISSIONS);
        $snapshots = map($urls, fn (ArtisanUrl $url): Snapshot => $this->snapshots->get($url, false));

        $everything = $this->processor->getAllOfferStatuses($snapshots);
        $resolved = $this->processor->getResolvedOfferStatuses($everything);

        $this->logIssues($resolved, $artisan);

        return $this->getArtisanChangesGiven($artisan, $resolved);
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

    private function getArtisanChangesGiven(Artisan $artisan, OfferStatusResult $offerStatuses): ArtisanChanges
    {
        $result = new ArtisanChanges($artisan);
        $result->getChanged()->getVolatileData()->setCsTrackerIssue($offerStatuses->csTrackerIssue);
        $result->getChanged()->getVolatileData()->setLastCsUpdate($offerStatuses->lastCsUpdate);

        foreach ([true, false] as $status) {
            $offersMatchingStatus = array_filter($offerStatuses->offerStatuses, fn (OfferStatus $item): bool => $item->status === $status);

            $newValue = StringList::pack(map($offersMatchingStatus, fn (OfferStatus $item): string => ucfirst(strtolower($item->offer))));

            if ($status) {
                $result->getChanged()->setOpenFor($newValue);
            } else {
                $result->getChanged()->setClosedFor($newValue);
            }
        }

        return $result;
    }

    /**
     * @return ArtisanUrl[]
     */
    private function getUrlsToFetch(): array
    {
        return array_merge(...map(
            $this->artisans,
            fn (Artisan $artisan): array => $artisan->getUrlObjs(Field::URL_COMMISSIONS),
        ));
    }

    private function logIssues(OfferStatusResult $resolvedOfferStatuses, Artisan $artisan): void
    {
        foreach ($resolvedOfferStatuses->issues as $issue) {
            $context = merge(['artisan' => $artisan], $issue->toLogContext());

            $this->logger->notice($issue->description, $context);
        }
    }
}
