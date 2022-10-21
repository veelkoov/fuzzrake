<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArtisanRepository;
use App\Tasks\Miniatures\MiniaturesUpdater;
use App\Tasks\Miniatures\UpdateResult;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:data:set-miniatures')]
class DataSetMiniaturesCommand extends Command
{
    public function __construct(
        private readonly ArtisanRepository $artisanRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MiniaturesUpdater $updater,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('commit', null, null, 'Save changes in the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach (Artisan::wrapAll($this->artisanRepository->getAll()) as $artisan) {
            $makerId = $artisan->getLastMakerId();

            $result = $this->updater->update($artisan);

            switch ($result) {
                case UpdateResult::NO_CHANGE:
                    break;

                case UpdateResult::CLEARED:
                    $io->info("Cleared miniatures for $makerId.");
                    break;

                case UpdateResult::RETRIEVED:
                    $io->info("Retrieved miniatures for $makerId.");
                    break;

                default:
                    if (!is_string($result)) {
                        $result = $result->name;
                    }

                    $io->error("Error for $makerId. $result");
            }
        }

        if ($input->getOption('commit')) {
            $this->entityManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }
}
