<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ArtisanUrl;
use App\Repository\ArtisanUrlRepository;
use App\Service\WebpageSnapshotManager;
use App\Utils\Artisan\Fields;
use App\Utils\Web\HttpClientException;
use App\Utils\Web\Url;
use App\Utils\Web\WebsiteInfo;
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
        $this->addOption('refresh', 'r', null, 'Refresh pages cache (re-fetch)');
        $this->addOption('no-prefetch', null, null, 'Skip pre-fetch phase');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('refresh')) {
            $this->webpageSnapshotManager->clearCache();
        }

        $urls = $this->getUrlsToCheck();

        if (!$input->getOption('no-prefetch')) {
            $this->prefetchUrls($urls, $io);
        }

        $this->checkUrls($urls, $io);
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
                Fields::URL_SCRITCH_MINIATURE,
            ]);
        });
    }

    /**
     * @param ArtisanUrl[] $urls
     * @param SymfonyStyle $io
     */
    private function checkUrls(array $urls, SymfonyStyle $io): void
    {
        foreach ($urls as $url) {
            $error = false;

            try {
                if (WebsiteInfo::isLatent404($this->webpageSnapshotManager->get($url->getUrlObject()))) {
                    $error = 'Latent 404: '.$url->getUrl();
                }
            } catch (HttpClientException $e) {
                $error = $e->getMessage();
            }

            if ($error) {
                $artisan = $url->getArtisan();
                $contact = trim($artisan->getContactAllowed().' '.$artisan->getContactMethod().' '
                    .$artisan->getContactAddressPlain());
                $io->writeln($artisan->getLastMakerId().':'.$contact.':'.$url->getType().': '.$error);
            }
        }
    }

    /**
     * @param ArtisanUrl[] $urls
     * @param SymfonyStyle $io
     */
    private function prefetchUrls($urls, SymfonyStyle $io): void
    {
        $this->webpageSnapshotManager->prefetchUrls(array_map(function (ArtisanUrl $artisanUrl): Url {
            return $artisanUrl->getUrlObject();
        }, $urls), $io);
    }
}
