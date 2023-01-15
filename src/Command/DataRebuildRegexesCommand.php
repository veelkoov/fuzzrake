<?php

declare(strict_types=1);

namespace App\Command;

use App\Tracking\Regex\RegexPersistence;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:data:rebuild-regexes')]
class DataRebuildRegexesCommand extends Command
{
    public function __construct(
        private readonly RegexPersistence $patternManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->patternManager->rebuild();

        $io->success('Finished');

        return Command::SUCCESS;
    }
}
