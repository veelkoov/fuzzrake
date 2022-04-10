<?php

declare(strict_types=1);

namespace App\Command;

use App\Tasks\TrackerUpdates\TrackerTaskRunnerFactory;
use App\Tracker\TrackerException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TrackerRunUpdatesCommand extends Command
{
    protected static $defaultName = 'app:tracker:run-updates';

    private const OPT_REFETCH = 'refetch';
    private const OPT_COMMIT = 'commit';
    private const ARG_MODE = 'mode';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TrackerTaskRunnerFactory $factory,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption(self::OPT_REFETCH, null, null, 'Refresh cache (re-fetch pages)')
            ->addOption(self::OPT_COMMIT, null, null, 'Save changes in the database')
            ->addArgument(self::ARG_MODE, InputArgument::REQUIRED, 'Mode of work')
        ;
    }

    /**
     * @throws TrackerException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $task = $this->factory->get(
            $input->getArgument(self::ARG_MODE),
            $input->getOption(self::OPT_REFETCH),
            $input->getOption(self::OPT_COMMIT),
            $io,
        );

        $task->performUpdates();

        if ($input->getOption(self::OPT_COMMIT)) {
            $this->entityManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }
}
