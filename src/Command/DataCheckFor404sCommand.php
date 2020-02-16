<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ArtisanUrl;
use App\Repository\ArtisanUrlRepository;
use App\Service\WebpageSnapshotManager;
use App\Utils\Artisan\Fields;
use App\Utils\Web\HttpClientException;
use App\Utils\Web\WebsiteInfo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataCheckFor404sCommand extends Command
{
    private const SKIPPED_TYPES = [
        Fields::URL_OTHER,
        Fields::URL_SCRITCH,
        Fields::URL_SCRITCH_PHOTO,
        Fields::URL_SCRITCH_MINIATURE,
    ];

    const DEFAULT_LIMIT = 10;
    const ARG_LIMIT = 'limit';

    protected static $defaultName = 'app:data:check-for-404s'; // TODO: rename

    private ArtisanUrlRepository $artisanUrlRepository;
    private WebpageSnapshotManager $webpageSnapshotManager;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, WebpageSnapshotManager $webpageSnapshotManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->webpageSnapshotManager = $webpageSnapshotManager;
        $this->artisanUrlRepository = $entityManager->getRepository(ArtisanUrl::class);
    }

    protected function configure()
    {
        $this->addArgument(self::ARG_LIMIT, InputArgument::OPTIONAL, 'Number of URLs to check', self::DEFAULT_LIMIT);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $limit = $input->getArgument(self::ARG_LIMIT);
        if (!is_int($limit) || $limit <= 0 || $limit >= 100) {
            $io->error('Argument "'.self::ARG_LIMIT.'" must be a number between 1 and 100');

            return 1;
        }

        $urls = $this->artisanUrlRepository->getLeastRecentFetched($limit, self::SKIPPED_TYPES);

        $io->progressStart(count($urls));

        foreach ($urls as $url) {
            try {
                $this->webpageSnapshotManager->get($url);
            } catch (HttpClientException $e) {
                // Ignore - failure has been recorded
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();

        $io->progressFinish();
        $io->success('Finished');

        return 0;
    }

    /**
     * @param ArtisanUrl[] $urls
     */
    private function checkUrls(array $urls, SymfonyStyle $io): void // TODO: relocate to either manager or HTTP client
    {
        foreach ($urls as $url) {
            $error = false;

            try {
                if (WebsiteInfo::isLatent404($this->webpageSnapshotManager->get($url))) {
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
}
