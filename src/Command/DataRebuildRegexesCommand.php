<?php

declare(strict_types=1);

namespace App\Command;

use App\Tracker\RegexPersistence;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataRebuildRegexesCommand extends Command
{
    protected static $defaultName = 'app:data:rebuild-regexes';

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

        return 0;
    }
}
