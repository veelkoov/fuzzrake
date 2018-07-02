<?php

namespace App\Command;

use App\Entity\Artisan;
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->commissionStatusUpdateService->updateAll($io);

        $io->success('Finished');
    }
}
