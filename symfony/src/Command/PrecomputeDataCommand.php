<?php

namespace App\Command;

use App\Service\DataService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:precompute-data',
    description: 'Requests all cache-able stuff which takes a long time to compute.',
)]
class PrecomputeDataCommand extends Command
{
    public function __construct(
        private readonly DataService $dataService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->dataService->getCompletenessStats();
        $this->dataService->getProvidedInfoStats();
        $this->dataService->getCreatorsPublicDataJsonString();

        $io->success('Finished.');

        return Command::SUCCESS;
    }
}
