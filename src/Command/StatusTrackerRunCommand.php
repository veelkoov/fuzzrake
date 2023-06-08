<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand('app:status-tracker:run')]
class StatusTrackerRunCommand extends Command
{
    private const OPT_REFETCH = 'refetch'; // TODO
    private const OPT_COMMIT = 'commit'; // TODO

    protected function configure(): void
    {
        $this
            ->addOption(self::OPT_REFETCH, null, null, 'Refresh cache (re-fetch pages)') // TODO
            ->addOption(self::OPT_COMMIT, null, null, 'Save changes in the database') // TODO
        ;
    }
}
