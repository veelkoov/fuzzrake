<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CreatorRepository;
use App\Service\DataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:precompute-data',
    description: 'Requests all cache-able stuff which takes a long time to compute',
)]
final class PrecomputeDataCommand
{
    public function __construct(
        private readonly DataService $dataService,
        private readonly CreatorRepository $creatorRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
    ): int {
        $io->comment('Preloading creators');
        $progress = new ProgressBar($io, $this->creatorRepository->countAll());

        foreach ($this->dataService->getCreatorsData(true) as $_) {
            $this->entityManager->clear();
            $progress->advance();
        }
        $progress->finish();

        $io->comment('Provided info stats');
        $this->dataService->getProvidedInfoStats();

        $io->success('Finished.');

        return Command::SUCCESS;
    }
}
