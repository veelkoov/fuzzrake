<?php

declare(strict_types=1);

namespace App\Command;

use App\Data\Tidying\ArtisanChanges as CreatorChanges;
use App\Data\Tidying\FdvFactory;
use App\Data\Tidying\Printer;
use App\Filtering\DataRequests\Pagination;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:data:tidy')]
class DataTidyCommand extends Command
{
    private const string OPT_COMMIT = 'commit';
    private const string OPT_WITH_INACTIVE = 'with-inactive';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CreatorRepository $creatorRepository,
        private readonly FdvFactory $fdvFactory,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addOption(self::OPT_COMMIT, null, null, 'Save changes in the database')
            ->addOption(self::OPT_WITH_INACTIVE, null, null, 'Include hidden creators')
        ;
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fdv = $this->fdvFactory->create(new Printer($io));

        $first = 0;
        $total = 1; // Temporary false value to start the loop

        while ($first < $total) {
            $creatorsPage = $this->creatorRepository->getPaginated($first, Pagination::PAGE_SIZE);

            $total = $creatorsPage->count();
            $first += Pagination::PAGE_SIZE;

            foreach ($creatorsPage as $creator) {
                if (!$input->getOption(self::OPT_WITH_INACTIVE) && '' !== $creator->getInactiveReason()) {
                    continue;
                }

                $creatorFixWip = new CreatorChanges(Creator::wrap($creator));
                $fdv->perform($creatorFixWip);
                $creatorFixWip->apply();
            }

            if ($input->getOption(self::OPT_COMMIT)) {
                $this->entityManager->flush();
            }
            $this->entityManager->clear();
        }

        if ($input->getOption(self::OPT_COMMIT)) {
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return Command::SUCCESS;
    }
}
