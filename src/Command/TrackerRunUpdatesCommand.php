<?php

declare(strict_types=1);

namespace App\Command;

use App\Tasks\CommissionsStatusesUpdateFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TrackerRunUpdatesCommand extends Command
{
    private const O_REFETCH = 'refetch';
    private const O_COMMIT = 'commit';

    protected static $defaultName = 'app:update:commissions';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommissionsStatusesUpdateFactory $factory,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption(self::O_REFETCH, null, null, 'Refresh cache (re-fetch pages)');
        $this->addOption(self::O_COMMIT, null, null, 'Save changes in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $task = $this->factory->get($io,
            $input->getOption(self::O_REFETCH),
            !$input->getOption(self::O_COMMIT),
        );

        $task->updateAll();
        $this->entityManager->flush();

        if ($input->getOption(self::O_COMMIT)) {
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }
}
