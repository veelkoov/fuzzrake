<?php

declare(strict_types=1);

namespace App\Command;

use App\Data\Tidying\ArtisanChanges as CreatorChanges;
use App\Data\Tidying\FdvFactory;
use App\Data\Tidying\Printer;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
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
        $wantCommit = $input->getOption(self::OPT_COMMIT);

        $io = new SymfonyStyle($input, $output);
        $fdv = $this->fdvFactory->create(new Printer($io));

        $creators = $input->getOption(self::OPT_WITH_INACTIVE)
            ? $this->creatorRepository->getAllPaged(flushAfterPage: $wantCommit)
            : $this->creatorRepository->getActivePaged(flushAfterPage: $wantCommit);

        foreach ($creators as $creatorE) {
            $creatorFixWip = new CreatorChanges(Creator::wrap($creatorE));
            $fdv->perform($creatorFixWip);
            $creatorFixWip->apply();
        }

        if ($wantCommit) {
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return Command::SUCCESS;
    }
}
