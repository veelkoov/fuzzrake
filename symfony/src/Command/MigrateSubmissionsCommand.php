<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\SubmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-submissions',
)]
class MigrateSubmissionsCommand extends Command // TODO: Remove this https://github.com/veelkoov/fuzzrake/issues/290
{
    public function __construct(
        private readonly SubmissionRepository $submissionRepository, // @phpstan-ignore property.onlyWritten
        private readonly EntityManagerInterface $entityManager, // @phpstan-ignore property.onlyWritten
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        

        return Command::SUCCESS;
    }
}
