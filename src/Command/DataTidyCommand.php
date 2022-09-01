<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\FixerDifferValidator as FDV;
use App\Utils\Data\Manager;
use App\Utils\Data\Printer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:data:tidy')]
class DataTidyCommand extends Command
{
    private const OPT_COMMIT = 'commit';
    private const OPT_WITH_INACTIVE = 'with-inactive';
    private const ARG_CORRECTIONS_FILE = 'corrections-file';

    public function __construct(
        private readonly EntityManagerInterface $objectManager,
        private readonly ArtisanRepository $artisanRepo,
        private readonly FdvFactory $fdvFactory,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(self::OPT_COMMIT, null, null, 'Save changes in the database')
            ->addOption(self::OPT_WITH_INACTIVE, null, null, 'Include inactive artisans')
            ->addArgument(self::ARG_CORRECTIONS_FILE, InputArgument::REQUIRED, 'Corrections file path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fdv = $this->fdvFactory->create(new Printer($io));

        $manager = new Manager($this->logger, directivesFilePath: $input->getArgument(self::ARG_CORRECTIONS_FILE));

        $artisans = $input->getOption(self::OPT_WITH_INACTIVE) ? $this->artisanRepo->getAll() : $this->artisanRepo->getActive();

        foreach ($artisans as $artisan) {
            $artisanFixWip = new ArtisanChanges(Artisan::wrap($artisan));

            $manager->correctArtisan($artisanFixWip->getChanged());

            $fdv->perform($artisanFixWip, FDV::FIX | FDV::SHOW_DIFF | FDV::RESET_INVALID_PLUS_SHOW_FIX_CMD);
            $artisanFixWip->apply();
        }

        if ($input->getOption(self::OPT_COMMIT)) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }
}
