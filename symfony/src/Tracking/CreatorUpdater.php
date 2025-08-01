<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Entity\Event;
use App\Tracking\Data\AnalysisResults;
use App\Utils\Collections\StringList;
use App\Utils\Creator\Changes\Description;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\PackedStringList;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CreatorUpdater
{
    private readonly ContextLogger $logger;

    public function __construct(
        #[Autowire(service: 'monolog.logger.tracking')]
        LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->logger = new ContextLogger($logger);
    }

    public function applyResults(Creator $creator, AnalysisResults $analysisResults): void
    {
        $this->logger->resetContextFor($creator);

        $old = clone $creator;

        $volatileData = $creator->getVolatileData();
        $volatileData->setLastCsUpdate(UtcClock::now());
        $volatileData->setCsTrackerIssue($analysisResults->hasEncounteredIssues);

        $creator->setOpenFor($analysisResults->openFor->getValuesArray());
        $creator->setClosedFor($analysisResults->closedFor->getValuesArray());

        $differences = new Description($old, $creator);
        foreach ($differences->getList() as $change) {
            $this->logger->info($change);
        }

        $this->createEvent($old, $creator, $analysisResults->hasEncounteredIssues);
    }

    private function createEvent(Creator $old, Creator $new, bool $hasEncounteredIssues): void
    {
        $nowOpenFor = new StringList($new->getOpenFor())->minusAll($old->getOpenFor());
        $nowLongerOpenFor = new StringList($old->getOpenFor())->minusAll($new->getOpenFor());

        if ($nowOpenFor->isEmpty() && $nowLongerOpenFor->isEmpty()) {
            return;
        }

        $this->logger->info('Creating an event for the changes.');

        $event = new Event()
            ->setCheckedUrls(PackedStringList::pack($new->getCommissionsUrls()))
            ->setNowOpenFor(PackedStringList::pack($nowOpenFor->getValuesArray()))
            ->setNoLongerOpenFor(PackedStringList::pack($nowLongerOpenFor->getValuesArray()))
            ->setTrackingIssues($hasEncounteredIssues)
            ->setType(Event::TYPE_CS_UPDATED)
            ->setCreatorName($new->getName())
        ;

        $this->entityManager->persist($event);
    }
}
