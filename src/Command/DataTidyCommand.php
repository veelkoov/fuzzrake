<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DataTidying\ArtisanChanges;
use App\Utils\DataTidying\FdvFactory;
use App\Utils\DataTidying\Printer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:data:tidy')]
class DataTidyCommand extends Command
{
    private const OPT_COMMIT = 'commit';
    private const OPT_WITH_INACTIVE = 'with-inactive';

    public function __construct(
        private readonly EntityManagerInterface $objectManager,
        private readonly ArtisanRepository $artisanRepo,
        private readonly FdvFactory $fdvFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(self::OPT_COMMIT, null, null, 'Save changes in the database')
            ->addOption(self::OPT_WITH_INACTIVE, null, null, 'Include inactive artisans')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fdv = $this->fdvFactory->create(new Printer($io));

        $artisans = $input->getOption(self::OPT_WITH_INACTIVE) ? $this->artisanRepo->getAll() : $this->artisanRepo->getActive();

        foreach ($artisans as $artisan) {
            $artisanFixWip = new ArtisanChanges(Artisan::wrap($artisan));

            $fdv->perform($artisanFixWip);
            $artisanFixWip->apply();
        }

        if ($input->getOption(self::OPT_COMMIT)) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return Command::SUCCESS;
    }
}
