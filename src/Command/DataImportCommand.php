<?php

declare(strict_types=1);

namespace App\Command;

use App\Tasks\DataImportFactory;
use App\Utils\DataInputException;
use App\Utils\IuSubmissions\Finder;
use App\Utils\IuSubmissions\Manager;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataImportCommand extends Command
{
    protected static $defaultName = 'app:data:import';

    private DataImportFactory $dataImportFactory;
    private EntityManagerInterface $objectManager;

    public function __construct(DataImportFactory $factory, EntityManagerInterface $objectManager)
    {
        parent::__construct();

        $this->dataImportFactory = $factory;
        $this->objectManager = $objectManager;
    }

    protected function configure()
    {
        $this->addOption('commit', null, null, 'Save changes in the database');
        $this->addOption('fix-mode', null, null, 'Show import command for fixes');
        $this->addArgument('import-dir', InputArgument::REQUIRED, 'Import directory path');
        $this->addArgument('corrections-file', InputArgument::REQUIRED, 'Corrections file path');
    }

    /**
     * @throws DataInputException|JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $import = $this->dataImportFactory->get(Manager::createFromFile($input->getArgument('corrections-file')),
            $io, $input->getOption('fix-mode'));

        $import->import(Finder::getFrom($input->getArgument('import-dir')));

        if ($input->getOption('commit')) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }
}
