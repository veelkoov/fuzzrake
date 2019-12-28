<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DataImportFactory;
use App\Utils\Import\CSV;
use App\Utils\Import\ImportException;
use App\Utils\Import\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataImportCommand extends Command
{
    protected static $defaultName = 'app:data:import';

    /**
     * @var DataImportFactory
     */
    private $dataImportFactory;

    /**
     * @var EntityManagerInterface
     */
    private $objectManager;

    public function __construct(DataImportFactory $factory, EntityManagerInterface $objectManager)
    {
        $this->dataImportFactory = $factory;
        $this->objectManager = $objectManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('commit', null, null, 'Save changes in the database');
        $this->addOption('fix-mode', null, null, 'Show import command for fixes');
        $this->addArgument('import-file', InputArgument::REQUIRED, 'Import file path');
        $this->addArgument('corrections-file', InputArgument::REQUIRED, 'Corrections file path');
    }

    /**
     * @throws ImportException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $import = $this->dataImportFactory->get(Manager::createFromFile($input->getArgument('corrections-file')),
            $io, $input->getOption('fix-mode'));

        $import->import(CSV::arrayFromFile($input->getArgument('import-file')));

        if ($input->getOption('commit')) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }
    }
}
