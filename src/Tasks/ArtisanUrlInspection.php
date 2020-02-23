<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Entity\ArtisanUrl;
use App\Repository\ArtisanUrlRepository;
use App\Service\WebpageSnapshotManager;
use App\Utils\Artisan\Fields;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

final class ArtisanUrlInspection
{
    private const SKIPPED_TYPES = [
        Fields::URL_OTHER,
        Fields::URL_SCRITCH,
        Fields::URL_SCRITCH_PHOTO,
        Fields::URL_SCRITCH_MINIATURE,
    ];

    private ArtisanUrlRepository $artisanUrlRepository;
    private WebpageSnapshotManager $webpageSnapshotManager;
    private EntityManagerInterface $entityManager;
    private SymfonyStyle $io;

    public function __construct(EntityManagerInterface $entityManager, WebpageSnapshotManager $webpageSnapshotManager, SymfonyStyle $io)
    {
        $this->entityManager = $entityManager;
        $this->webpageSnapshotManager = $webpageSnapshotManager;
        $this->artisanUrlRepository = $entityManager->getRepository(ArtisanUrl::class);
        $this->io = $io;
    }

    public function inspect(int $limit): void
    {
        $urls = $this->artisanUrlRepository->getLeastRecentFetched($limit, self::SKIPPED_TYPES);

        $this->io->progressStart(count($urls));

        foreach ($urls as $url) {
            try {
                $this->webpageSnapshotManager->get($url, true);
            } catch (ExceptionInterface $e) {
                // Ignore - failure has been recorded
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
    }

    //    private function checkUrls(array $urls, SymfonyStyle $io): void // TODO: relocate to either manager or HTTP client
//    {
//        foreach ($urls as $url) {
//            $error = false;
//
//            try {
//                if (WebsiteInfo::isLatent404($this->webpageSnapshotManager->get($url))) {
//                    $error = 'Latent 404: '.$url->getUrl();
//                }
//            } catch (HttpClientException $e) {
//                $error = $e->getMessage();
//            }
//
//            if ($error) {
//                $artisan = $url->getArtisan();
//                $contact = trim($artisan->getContactAllowed().' '.$artisan->getContactMethod().' '
//                    .$artisan->getContactAddressPlain());
//                $io->writeln($artisan->getLastMakerId().':'.$contact.':'.$url->getType().': '.$error);
//            }
//        }
//    }
}
