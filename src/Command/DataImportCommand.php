<?php

declare(strict_types=1);

namespace App\Command;

use App\Tasks\DataImportFactory;
use App\Utils\Data\Manager;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\IuSubmissions\Finder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataImportCommand extends Command
{
    protected static $defaultName = 'app:data:import';

    public function __construct(
        private readonly DataImportFactory $dataImportFactory,
        private readonly EntityManagerInterface $objectManager,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('commit', null, null, 'Save changes in the database')
            ->addOption('fix-mode', null, null, 'Show import command for fixes')
            ->addArgument('import-dir', InputArgument::REQUIRED, 'Import directory path')
            ->addArgument('corrections-file', InputArgument::REQUIRED, 'Corrections file path')
            ->addArgument('only-after', InputArgument::OPTIONAL, 'Process requests no earlier than this date')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if (null !== $input->getArgument('only-after')) {
                $onlyAfter = DateTimeUtils::getUtcAt($input->getArgument('only-after') ?? '');
            } else {
                $onlyAfter = null;
            }
        } catch (DateTimeException $e) {
            $io->error('Invalid start date argument(s), '.$e->getMessage());

            return 1;
        }

        $import = $this->dataImportFactory->get(Manager::createFromFile($input->getArgument('corrections-file')),
            $io, $input->getOption('fix-mode'));

        $import->import(Finder::getFrom($input->getArgument('import-dir'), $onlyAfter));

        if ($input->getOption('commit')) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }
}
