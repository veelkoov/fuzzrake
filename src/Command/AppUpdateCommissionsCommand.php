<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CommissionStatusUpdateService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppUpdateCommissionsCommand extends Command
{
    protected static $defaultName = 'app:update:commissions';

    /**
     * @var CommissionStatusUpdateService
     */
    private $commissionStatusUpdateService;

    public function __construct(CommissionStatusUpdateService $commissionStatusUpdateService)
    {
        $this->commissionStatusUpdateService = $commissionStatusUpdateService;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('refresh', 'r', null, 'Refresh pages cache (re-fetch)');
        $this->addOption('dry-run', 'd', null, 'Dry run (don\'t update the DB)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->commissionStatusUpdateService->updateAll($io, $input->getOption('refresh'), $input->getOption('dry-run'));

        $io->success('Finished');
    }
}
