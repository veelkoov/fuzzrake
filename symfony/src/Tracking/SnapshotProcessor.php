<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Utils\Collections\StringList;
use App\Utils\Web\Snapshots\Snapshot;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SnapshotProcessor
{
    public function __construct(
        /* @phpstan-ignore property.onlyWritten (TODO: Use) */
        #[Autowire(service: 'monolog.logger.tracking')]
        private readonly LoggerInterface $logger,
    ) {
    }

    public function analyse(Snapshot $snapshot): AnalysisResult // FIXME
    {
        if (false !== stripos($snapshot->contents, 'commissions: open')) {
            return new AnalysisResult(new StringList(['commissions']), new StringList(), false);
        }

        if (false !== stripos($snapshot->contents, 'commissions: close')) {
            return new AnalysisResult(new StringList(), new StringList(['commissions']), false);
        }

        return new AnalysisResult(new StringList(), new StringList(), true);
    }
}
