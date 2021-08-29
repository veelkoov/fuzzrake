<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArtisanRepository;
use App\Tasks\Miniatures\FurtrackMiniatures;
use App\Tasks\Miniatures\ScritchMiniatures;
use App\Utils\Artisan\SmartAccessDecorator as Smart;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class DataSetMiniaturesCommand extends Command
{
    public function __construct(
        private ArtisanRepository $artisanRepository,
        private EntityManagerInterface $entityManager,
        private ScritchMiniatures $scritch,
        private FurtrackMiniatures $furtrack,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:data:set-miniatures')
            ->addOption('commit', null, null, 'Save changes in the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach (Smart::wrapAll($this->artisanRepository->getAll()) as $artisan) {
            $pictureUrls = array_filter(explode("\n", $artisan->getPhotoUrls()));

            if (empty($pictureUrls)) {
                $artisan->setMiniatureUrls('');
                continue;
            }

            if (count($pictureUrls) === count(array_filter(explode("\n", $artisan->getMiniatureUrls())))) {
                continue;
            }

            $unsupported = $this->filterUnsupportedUrls($pictureUrls);
            if (0 !== count($unsupported)) {
                $io->error('Unsupported URLs for '.$artisan->getLastMakerId().': '.implode(', ', $unsupported));
                continue;
            }

            try {
                $miniatureUrls = $this->retrieveMiniatureUrls($pictureUrls);
            } catch (ExceptionInterface | JsonException | LogicException $e) {
                $io->error('Failed: '.$artisan->getLastMakerId().', '.$e->getMessage());
                continue;
            }

            $artisan->setMiniatureUrls(implode("\n", $miniatureUrls));
            $io->writeln('Retrieved miniatures for '.$artisan->getLastMakerId());
        }

        if ($input->getOption('commit')) {
            $this->entityManager->flush();
            $io->success('Finished and saved');
        } else {
            $io->success('Finished without saving');
        }

        return 0;
    }

    /**
     * @param string[] $pictureUrls
     *
     * @return string[]
     *
     * @throws JsonException|LogicException|ExceptionInterface
     */
    private function retrieveMiniatureUrls(array $pictureUrls): array
    {
        $result = [];

        foreach ($pictureUrls as $url) {
            if ($this->furtrack->supportsUrl($url)) {
                $result[] = $this->furtrack->getMiniatureUrl($url);
            } else {
                $result[] = $this->scritch->getMiniatureUrl($url);
            }
        }

        return $result;
    }

    private function filterUnsupportedUrls(array $pictureUrls): array
    {
        $result = [];

        foreach ($pictureUrls as $url) {
            if (!$this->scritch->supportsUrl($url) && !$this->furtrack->supportsUrl($url)) {
                $result[] = $url;
            }
        }

        return $result;
    }
}
