<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DataSetMakerIdsCommand extends Command
{
    public function __construct(
        private ArtisanRepository $artisanRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:data:set-maker-ids')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (Artisan::wrapAll($this->artisanRepository->findAll()) as $artisan) {
            if (empty($artisan->getAllMakerIdsArr())) {
                $artisan->setFormerMakerIds('M'.str_pad((string) $artisan->getId(), 6, '0', STR_PAD_LEFT));
            }
        }

        $this->entityManager->flush();

        return 0;
    }
}
