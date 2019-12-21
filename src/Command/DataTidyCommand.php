<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\FixerDifferValidator as FDV;
use App\Utils\Data\Printer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataTidyCommand extends Command
{
    protected static $defaultName = 'app:data:tidy';

    /**
     * @var ArtisanRepository
     */
    private $artisanRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var FdvFactory
     */
    private $fdvFactory;

    public function __construct(ObjectManager $objectManager, FdvFactory $fdvFactory)
    {
        $this->artisanRepository = $objectManager->getRepository(Artisan::class);
        $this->objectManager = $objectManager;
        $this->fdvFactory = $fdvFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('commit', null, null, 'Save changes in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $fdv = $this->fdvFactory->create(new Printer($io));

        foreach ($this->artisanRepository->findAll() as $artisan) {
            $fdv->perform($artisan, FDV::FIX | FDV::SHOW_DIFF | FDV::RESET_INVALID_PLUS_SHOW_FIX_CMD);
        }

        if ($input->getOption('commit')) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }
    }
}
