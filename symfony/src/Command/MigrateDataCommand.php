<?php

namespace App\Command;

use App\Repository\ArtisanRepository as CreatorRepository;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\PackedStringList;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-data',
    description: 'Move data from languages, prod. models, features, order types and styles to values table.',
)]
class MigrateDataCommand extends Command // TODO: Temporary. Remove.
{
    public function __construct(
        private readonly CreatorRepository $creatorRepository,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $totalCount = $this->creatorRepository->countAll();
        $progressBar = $io->createProgressBar($totalCount);
        $progressBar->display();

        $creators = $this->creatorRepository->getAllPaged(flushAfterPage: true);

        foreach ($creators as $creatorE) {
            $creator = Creator::wrap($creatorE);

            $creator->setLanguages(PackedStringList::unpack($creatorE->getLegacyLanguages()));

            $creator->setProductionModels(PackedStringList::unpack($creatorE->getLegacyProductionModels()));

            $creator->setFeatures(PackedStringList::unpack($creatorE->getLegacyFeatures()));
            $creator->setOtherFeatures(PackedStringList::unpack($creatorE->getLegacyOtherFeatures()));

            $creator->setOrderTypes(PackedStringList::unpack($creatorE->getLegacyOrderTypes()));
            $creator->setOtherOrderTypes(PackedStringList::unpack($creatorE->getLegacyOtherOrderTypes()));

            $creator->setStyles(PackedStringList::unpack($creatorE->getLegacyStyles()));
            $creator->setOtherStyles(PackedStringList::unpack($creatorE->getLegacyOtherStyles()));

            $creatorE->clearLegacyFields();

            $progressBar->advance();
        }

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
