<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CommissionStatusUpdateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateCommissionsCommand extends Command
{
    protected static $defaultName = 'app:update:commissions';

    private CommissionStatusUpdateService $commissionStatusUpdateService;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, CommissionStatusUpdateService $commissionStatusUpdateService)
    {
        $this->commissionStatusUpdateService = $commissionStatusUpdateService;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('refresh', 'r', null, 'Refresh pages cache (re-fetch)');
        $this->addOption('dry-run', 'd', null, 'Dry run (don\'t update the DB)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->commissionStatusUpdateService->updateAll($io, $input->getOption('refresh'), $input->getOption('dry-run'));
        $this->entityManager->flush();

        $io->success('Finished');

        return 0;
    }
}
