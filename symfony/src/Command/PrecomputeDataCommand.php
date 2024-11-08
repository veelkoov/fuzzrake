<?php

namespace App\Command;

use App\Service\DataService;
use Override;
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

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->comment('Creators public data JSON string');
        $this->dataService->getCreatorsPublicDataJsonString();
        $io->comment('Completeness stats');
        $this->dataService->getCompletenessStats();
        $io->comment('Provided info stats');
        $this->dataService->getProvidedInfoStats();

        $io->success('Finished.');

        return Command::SUCCESS;
    }
}
