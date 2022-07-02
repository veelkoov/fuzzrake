<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\EventRepository;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:events:remove-tmp-tracking-failures')]
class EventsRemoveTmpTrackingFailuresCommand extends Command // FIXME #93
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventRepository $eventRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Get rid of X -> unknown -> x state changes between two given dates')
            ->addArgument('date1', InputArgument::REQUIRED, 'Date when failures occurred')
            ->addArgument('date2', InputArgument::REQUIRED, 'Date when failures were corrected')
            ->addOption('commit', null, InputOption::VALUE_NONE, 'Save changes in the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $date1 = UtcClock::at($input->getArgument('date1') ?? '');
            $date2 = UtcClock::at($input->getArgument('date2') ?? '');
        } catch (DateTimeException $e) {
            $io->error('Invalid/missing date argument(s), '.$e->getMessage());

            return 1;
        }

        $events = $this->eventRepository->selectTrackingTmpFailures($date1, $date2);
        $io->note('Found '.count($events).' events');

        if ($input->getOption('commit')) {
            array_walk($events, [$this->entityManager, 'remove']);
            $this->entityManager->flush();

            $io->success('Events found were removed');
        } else {
            $io->success('Finished without removing');
        }

        return 0;
    }
}
