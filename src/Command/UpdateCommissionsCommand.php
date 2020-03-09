<?php

declare(strict_types=1);

namespace App\Command;

use App\Tasks\CommissionsStatusesUpdateFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateCommissionsCommand extends Command
{
    protected static $defaultName = 'app:update:commissions';

    private CommissionsStatusesUpdateFactory $factory;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, CommissionsStatusesUpdateFactory $factory)
    {
        $this->factory = $factory;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('refetch', null, null, 'Refresh cache (re-fetch pages)');
        $this->addOption('commit', null, null, 'Save changes in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $task = $this->factory->get($io, $input->getOption('refetch'), !$input->getOption('commit'));
        $task->updateAll();

        $this->entityManager->flush();

        if ($input->getOption('commit')) {
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }
}
