<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ArtisanUrl;
use App\Repository\ArtisanUrlRepository;
use App\Service\WebpageSnapshotManager;
use App\Utils\Artisan\Fields;
use App\Utils\Web\HttpClientException;
use App\Utils\Web\Url;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataCheckFor404sCommand extends Command
{
    protected static $defaultName = 'app:data:check-for-404s';

    /**
     * @var ArtisanUrlRepository
     */
    private $artisanUrlRepository;

    /**
     * @var WebpageSnapshotManager
     */
    private $webpageSnapshotManager;

    public function __construct(ArtisanUrlRepository $artisanUrlRepository, WebpageSnapshotManager $webpageSnapshotManager)
    {
        parent::__construct();

        $this->artisanUrlRepository = $artisanUrlRepository;
        $this->webpageSnapshotManager = $webpageSnapshotManager;
    }

    protected function configure()
    {
        // TODO: refresh
        // TODO: no-prefetch
//        $this
//            ->setDescription('Add a short description for your command')
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
//        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $urls = $this->getUrlsToCheck();

        $this->webpageSnapshotManager->prefetchUrls(array_map(function (ArtisanUrl $artisanUrl): Url {
            return $artisanUrl->getUrlObject();
        }, $urls), $io);

        foreach ($urls as $url) {
            try {
                $this->webpageSnapshotManager->get($url->getUrlObject());
            } catch (HttpClientException $e) {
                $io->writeln($url->getArtisan()->getLastMakerId().':'.$url->getType().': '.$e->getMessage());
            }
        }
    }

    /**
     * @return ArtisanUrl[]
     */
    private function getUrlsToCheck(): array
    {
        return array_filter($this->artisanUrlRepository->findAll(), function (ArtisanUrl $artisanUrl): bool {
            return !in_array($artisanUrl->getType(), [
                Fields::URL_OTHER,
                Fields::URL_SCRITCH,
                Fields::URL_SCRITCH_PHOTO,
            ]);
        });
    }
}
