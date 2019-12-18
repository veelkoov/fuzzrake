<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\Data\Differ;
use App\Utils\Data\Fixer;
use App\Utils\Data\ValidatorFactory;
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
     * @var ValidatorFactory
     */
    private $validatorFactory;

    public function __construct(ObjectManager $objectManager, Fixer $fixer, ValidatorFactory $validatorFactory)
    {
        $this->artisanRepository = $objectManager->getRepository(Artisan::class);
        $this->objectManager = $objectManager;
        $this->fixer = $fixer;
        $this->validatorFactory = $validatorFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('commit', null, null, 'Save changes in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $validator = $this->validatorFactory->create($io);
        $differ = new Differ($io);

        foreach ($this->artisanRepository->findAll() as $artisan) {
            $originalArtisan = clone $artisan;
            $this->fixer->fix($artisan);
            $differ->showDiff($originalArtisan, $artisan);

            if (!$validator->validate($artisan)) {
                $this->objectManager->refresh($artisan);
            }
        }

        if ($input->getOption('commit')) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }
    }
}
