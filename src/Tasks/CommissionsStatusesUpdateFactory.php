<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Service\WebpageSnapshotManager;
use App\Utils\Tracking\CommissionsStatusParser;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CommissionsStatusesUpdateFactory
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private WebpageSnapshotManager $webpageSnapshotManager,
        private CommissionsStatusParser $commissionsStatusParser,
    ) {
    }

    public function get(SymfonyStyle $io, bool $refetch, bool $dryRun): CommissionsStatusesUpdate
    {
        return new CommissionsStatusesUpdate($this->logger, $this->entityManager, $this->webpageSnapshotManager,
            $this->commissionsStatusParser, $io, $refetch, $dryRun);
    }
}
