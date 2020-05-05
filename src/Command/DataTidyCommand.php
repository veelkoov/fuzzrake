<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\Data\ArtisanFixWip;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\FixerDifferValidator as FDV;
use App\Utils\Data\Printer;
use App\Utils\DataInput\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataTidyCommand extends Command
{
    protected static $defaultName = 'app:data:tidy';

    /**
     * @var ArtisanRepository|ObjectRepository
     */
    private ObjectRepository $artisanRepository;

    private EntityManagerInterface $objectManager;
    private FdvFactory $fdvFactory;

    public function __construct(EntityManagerInterface $objectManager, FdvFactory $fdvFactory)
    {
        $this->artisanRepository = $objectManager->getRepository(Artisan::class);
        $this->objectManager = $objectManager;
        $this->fdvFactory = $fdvFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('commit', null, null, 'Save changes in the database');
        $this->addArgument('corrections-file', InputArgument::OPTIONAL, 'Corrections file path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fdv = $this->fdvFactory->create(new Printer($io));

        $manager = Manager::createFromFile($input->getArgument('corrections-file') ?: '/dev/null');

        foreach ($this->artisanRepository->findAll() as $artisan) {
            $artisanFixWip = new ArtisanFixWip($artisan);

            $manager->correctArtisan($artisanFixWip->getFixed());

            $fdv->perform($artisanFixWip, FDV::FIX | FDV::SHOW_DIFF | FDV::RESET_INVALID_PLUS_SHOW_FIX_CMD);
        }

        if ($input->getOption('commit')) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }
}
