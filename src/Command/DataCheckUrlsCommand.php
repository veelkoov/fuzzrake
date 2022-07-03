<?php

declare(strict_types=1);

namespace App\Command;

use App\Tasks\ArtisanUrlInspectionFactory;
use App\Utils\Parse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:data:check-urls')]
class DataCheckUrlsCommand extends Command
{
    private const DEFAULT_LIMIT = 10;
    private const OPT_LIMIT = 'limit';

    public function __construct(
        private readonly ArtisanUrlInspectionFactory $factory,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(self::OPT_LIMIT, '', InputOption::VALUE_REQUIRED, 'Number of URLs to check', self::DEFAULT_LIMIT)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $limit = Parse::int($input->getOption(self::OPT_LIMIT));
        if ($limit <= 0 || $limit > 100) {
            $io->error('Value of "'.self::OPT_LIMIT.'" must be a number between 1 and 100');

            return 1;
        }

        $task = $this->factory->get($io);
        $task->inspect($limit);

        $this->entityManager->flush();
        $io->success('Finished');

        return 0;
    }
}
