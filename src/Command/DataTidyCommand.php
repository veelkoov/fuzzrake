<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArtisanRepository;
use App\Utils\DataFixer;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface
     */
    private $objectManager;

    public function __construct(ArtisanRepository $artisanRepository, EntityManagerInterface $objectManager)
    {
        $this->artisanRepository = $artisanRepository;
        $this->objectManager = $objectManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('commit', null, null, 'Save changes in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $fixer = new DataFixer($io, true);

        foreach ($this->artisanRepository->findAll() as $artisan) {
            $fixer->fixArtisanData($artisan);
        }

        foreach ($this->artisanRepository->findAll() as $artisan) {
            $fixer->validateArtisanData($artisan);
        }

        if ($input->getOption('commit')) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }
    }
}
