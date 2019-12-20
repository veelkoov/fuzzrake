<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\FixedArtisan;
use App\Utils\Data\Fixer;
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
     * @var Fixer
     */
    private $fixer;

    /**
     * @var FdvFactory
     */
    private $fdvFactory;

    public function __construct(ObjectManager $objectManager, Fixer $fixer, FdvFactory $fdvFactory)
    {
        $this->artisanRepository = $objectManager->getRepository(Artisan::class);
        $this->objectManager = $objectManager;
        $this->fixer = $fixer;
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
        $fdv = $this->fdvFactory->create($io);

        $artisans = $this->getFixed($this->artisanRepository->findAll());

        foreach ($artisans as $artisan) {
            $fdv->showDiffFixed($artisan);
            $fdv->resetInvalidFields($artisan, true);
        }

        if ($input->getOption('commit')) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }
    }

    /**
     * @param Artisan[] $artisans
     *
     * @return FixedArtisan[]
     */
    private function getFixed(array $artisans): array
    {
        return array_map(function (Artisan $artisan) {
            return $this->fixer->getFixed($artisan);
        }, $artisans);
    }
}
