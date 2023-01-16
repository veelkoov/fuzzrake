<?php

declare(strict_types=1);

namespace App\Tracking\OfferStatus;

use App\Tracking\Exception\TrackerException;
use App\Tracking\Issue;
use App\Tracking\TextParser;
use App\Tracking\Web\WebpageSnapshot\Snapshot;
use TRegx\SafeRegex\Exception\RuntimePregException;

class OffersStatusesProcessor
{
    public function __construct(
        private readonly TextParser $parser,
    ) {
    }

    /**
     * @param list<Snapshot> $snapshots
     */
    public function getOffersStatuses(array $snapshots): OffersStatusesResult
    {
        $intermediate = $this->getAllOfferStatuses($snapshots);

        return $this->getResolvedOfferStatuses($intermediate);
    }

    /**
     * @param list<Snapshot> $snapshots
     */
    private function getAllOfferStatuses(array $snapshots): OffersStatusesResult
    {
        $statuses = [];
        $issues = [];
        $lastCsUpdate = null;
        $csTrackerIssue = false;

        foreach ($snapshots as $snapshot) {
            $lastCsUpdate = $snapshot->retrievedAt; // Multiple snapshots are retrieved at similar times

            try {
                $allOfferStatuses = $this->parser->getOfferStatuses($snapshot);

                if (0 === count($allOfferStatuses)) {
                    $issues[] = new Issue('No statuses detected in URL', url: $snapshot->url);
                    $csTrackerIssue = true;
                }

                array_push($statuses, ...$allOfferStatuses);
            } catch (TrackerException|RuntimePregException $exception) {
                $issues[] = new Issue('Exception caught while detecting statuses in URL', url: $snapshot->url, exception: $exception);
                $csTrackerIssue = true;
            }
        }

        return new OffersStatusesResult($statuses, $lastCsUpdate, $csTrackerIssue, $issues);
    }

    private function getResolvedOfferStatuses(OffersStatusesResult $input): OffersStatusesResult
    {
        $statuses = [];
        $issues = $input->issues;
        $lastCsUpdate = $input->lastCsUpdate;
        $csTrackerIssue = $input->csTrackerIssue;

        foreach ($input->offersStatuses as $offerStatus) {
            if (!array_key_exists($offerStatus->offer, $statuses)) {
                // This is a status for an offer we didn't have previously
                $statuses[$offerStatus->offer] = $offerStatus;

                continue;
            }

            // At this point for sure there was some kind of issue
            $csTrackerIssue = true;

            // We have at best a duplicated offer
            $issues[] = new Issue('Duplicated status detected', $offerStatus->offer);

            $previousStatus = $statuses[$offerStatus->offer];

            if (null === $previousStatus) {
                // We have a 3rd+ offer with different statuses
                $issues[] = new Issue('Contradicting statuses detected (more than once)', $offerStatus->offer);

                continue;
            }

            if ($previousStatus->status !== $offerStatus->status) {
                // We have a 2nd offer and the status differs
                $issues[] = new Issue('Contradicting statuses detected', $offerStatus->offer);

                $statuses[$offerStatus->offer] = null;
            }
        }

        $statuses = array_values(array_filter($statuses)); // Get rid of any nulls coming from contradicting statuses

        return new OffersStatusesResult($statuses, $lastCsUpdate, $csTrackerIssue, $issues);
    }
}
