<?php

declare(strict_types=1);

namespace App\Command;

use App\Tasks\DataImportFactory;
use App\Utils\Data\Manager;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\IuSubmissions\Finder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:data:import')]
class DataImportCommand extends Command
{
    public function __construct(
        private readonly DataImportFactory $dataImportFactory,
        private readonly EntityManagerInterface $objectManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('commit', null, null, 'Save changes in the database')
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
                // @phpstan-ignore-next-line - It can be null
                $onlyAfter = UtcClock::at($input->getArgument('only-after') ?? '');
            } else {
                $onlyAfter = null;
            }
        } catch (DateTimeException $e) {
            $io->error('Invalid start date argument(s), '.$e->getMessage());

            return 1;
        }

        $manager = Manager::createFromFile($input->getArgument('corrections-file'));
        $import = $this->dataImportFactory->get($manager, $io);

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
