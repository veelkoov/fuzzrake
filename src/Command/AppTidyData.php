<?php

namespace App\Command;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppTidyData extends Command
{
    protected static $defaultName = 'app:tidy-data';

    /**
     * @var ArtisanRepository
     */
    private $artisanRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(ArtisanRepository $artisanRepository, ObjectManager $objectManager)
    {
        $this->artisanRepository = $artisanRepository;
        $this->objectManager = $objectManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('dry-run', 'd', null, 'Dry run (don\'t update the DB)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->artisanRepository->findAll() as $artisan) {
            $this->fixArtisanData($artisan, $io);
        }

        if (!$input->getOption('dry-run')) {
            $this->objectManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }
    }

    private function fixArtisanData(Artisan $artisan, SymfonyStyle $io): void
    {
        $artisan->setFeatures($this->fixList($artisan->getFeatures(), $io));
        $artisan->setStyles($this->fixList($artisan->getStyles(), $io));
//        $artisan->setTypes($this->fixList($artisan->getTypes(), $io));
    }

    private function fixList(string $input, SymfonyStyle $io): string
    {
        $list = preg_split('#[;,\n]#', $input);
        $list = array_map('trim', $list);
        $list = array_filter($list);
        sort($list);
        $result = implode(', ', $list);
        $result = str_replace(['Follow me eyes', 'Adjustable ears / wiggle ears'], ['Follow-me eyes', 'Adjustable/wiggle ears'], $result);

        if ($result != $input) {
            $io->text("\t---\n$input\n\t=>\n$result\n");
        }

        return $result;
    }
}
