<?php

declare(strict_types=1);

namespace App\Tracking;

use App\DataDefinitions\Fields\Field;
use App\Entity\ArtisanUrl;
use App\Entity\EventFactory;
use App\IuHandling\Changes\Description;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tracking\OfferStatus\OffersStatusesProcessor;
use App\Tracking\OfferStatus\OffersStatusesResult;
use App\Tracking\OfferStatus\OfferStatus;
use App\Tracking\Web\WebpageSnapshot\Snapshot;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
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

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly ArtisanRepository $repository,
        private readonly OffersStatusesProcessor $processor,
        private readonly WebpageSnapshotManager $snapshots,
        private readonly bool $refetch,
        private readonly SymfonyStyle $io,
    ) {
        $this->artisans = Artisan::wrapAll($this->repository->findAll());
    }

    public function performUpdates(): void
    {
        $urls = $this->getUrlsToFetch();
        $this->snapshots->prefetchUrls($urls, $this->refetch, $this->io);

        foreach ($this->artisans as $artisan) {
            $urls = $artisan->getUrlObjs(Field::URL_COMMISSIONS);
            $snapshots = map($urls, fn (ArtisanUrl $url): Snapshot => $this->snapshots->get($url, false));

            $offerStatusResult = $this->processor->getOffersStatuses($snapshots);
            $this->logIssues($offerStatusResult, $artisan);

            $updates = $this->applyUpdatesFor($artisan, $offerStatusResult);
            $this->logArtisanUpdates($updates, $artisan);
        }
    }

    private function applyUpdatesFor(Artisan $artisan, OffersStatusesResult $offerStatuses): Description
    {
        $before = clone $artisan;

        $artisan->setCsTrackerIssue($offerStatuses->csTrackerIssue);
        $artisan->setCsLastCheck($offerStatuses->lastCsUpdate);

        foreach ([true, false] as $status) {
            $offersMatchingStatus = array_filter($offerStatuses->offersStatuses, fn (OfferStatus $item): bool => $item->status === $status);

            $newValue = StringList::pack(map($offersMatchingStatus, fn (OfferStatus $item): string => ucfirst(strtolower($item->offer))));

            if ($status) {
                $artisan->setOpenFor($newValue);
            } else {
                $artisan->setClosedFor($newValue);
            }
        }

        return new Description($before, $artisan);
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

    private function logIssues(OffersStatusesResult $resolvedOfferStatuses, Artisan $artisan): void
    {
        foreach ($resolvedOfferStatuses->issues as $issue) {
            $context = merge($issue->toLogContext(), ['artisan' => (string) $artisan]);

            $this->logger->notice($issue->description, $context);
        }
    }

    private function logArtisanUpdates(Description $updates, Artisan $artisan): void
    {
        foreach ($updates->getChanges() as $change) {
            $this->logger->info($change->getDescription(), ['artisan' => (string) $artisan]);

            if (Field::OPEN_FOR === $change->getField()) {
                $this->entityManager->persist(EventFactory::forStatusTracker($change, $artisan));
            }
        }
    }
}
