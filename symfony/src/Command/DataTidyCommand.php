<?php

declare(strict_types=1);

namespace App\Command;

use App\Data\Tidying\CreatorChanges;
use App\Data\Tidying\FdvFactory;
use App\Data\Tidying\Printer;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:data:tidy',
)]
final class DataTidyCommand
{
    public function __construct(
        private readonly CreatorRepository $creatorRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly FdvFactory $fdvFactory,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option(description: 'Save changes in the database', name: 'commit')] bool $wantCommit = false,
        #[Option(description: 'Include hidden creators', name: 'with-inactive')] bool $withInactive = false,
    ): int {
        $fdv = $this->fdvFactory->create(new Printer($io));

        $creators = $withInactive
            ? $this->creatorRepository->getAllPaged()
            : $this->creatorRepository->getActivePaged();

        foreach ($creators as $creatorE) {
            $creatorFixWip = new CreatorChanges(Creator::wrap($creatorE));
            $fdv->perform($creatorFixWip);
            $creatorFixWip->apply();
        }

        if ($wantCommit) {
            $this->entityManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return Command::SUCCESS;
    }
}
