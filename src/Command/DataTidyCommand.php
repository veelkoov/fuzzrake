<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\FixerDifferValidator as FDV;
use App\Utils\Data\Manager;
use App\Utils\Data\Printer;
use App\Utils\DataInputException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataTidyCommand extends Command
{
    private const OPT_COMMIT = 'commit';
    private const OPT_WITH_INACTIVE = 'with-inactive';
    private const ARG_CORRECTIONS_FILE = 'corrections-file';

    protected static $defaultName = 'app:data:tidy';

    private ArtisanRepository $artisanRepo;

    public function __construct(
        private EntityManagerInterface $objectManager,
        private FdvFactory $fdvFactory,
    ) {
        $this->artisanRepo = $objectManager->getRepository(Artisan::class);

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption(self::OPT_COMMIT, null, null, 'Save changes in the database');
        $this->addOption(self::OPT_WITH_INACTIVE, null, null, 'Include inactive artisans');
        $this->addArgument(self::ARG_CORRECTIONS_FILE, InputArgument::OPTIONAL, 'Corrections file path');
    }

    /**
     * @throws DataInputException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fdv = $this->fdvFactory->create(new Printer($io));

        $manager = Manager::createFromFile($input->getArgument(self::ARG_CORRECTIONS_FILE) ?: '/dev/null');

        $artisans = $input->getOption(self::OPT_WITH_INACTIVE) ? $this->artisanRepo->getAll() : $this->artisanRepo->getActive();

        foreach ($artisans as $artisan) {
            $artisanFixWip = new ArtisanChanges($artisan);

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
