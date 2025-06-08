<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DataService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:precompute-data',
    description: 'Requests all cache-able stuff which takes a long time to compute',
)]
final class PrecomputeDataCommand
{
    public function __construct(
        private readonly DataService $dataService,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
    ): int {
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
