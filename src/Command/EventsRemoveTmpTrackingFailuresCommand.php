<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EventsRemoveTmpTrackingFailuresCommand extends Command
{
    protected static $defaultName = 'app:events:remove-tmp-tracking-failures';

    private EntityManagerInterface $entityManager;

    /**
     * @var EventRepository|ObjectRepository
     */
    private ObjectRepository $eventRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->eventRepository = $this->entityManager->getRepository(Event::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Get rid of X -> unknown -> x state changes between two given dates')
            ->addArgument('date1', InputArgument::REQUIRED, 'Date when failures occurred')
            ->addArgument('date2', InputArgument::REQUIRED, 'Date when failures were corrected')
            ->addOption('commit', null, InputOption::VALUE_NONE, 'Save changes in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $date1 = DateTimeUtils::getUtcAt($input->getArgument('date1') ?? '');
            $date2 = DateTimeUtils::getUtcAt($input->getArgument('date2') ?? '');
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
