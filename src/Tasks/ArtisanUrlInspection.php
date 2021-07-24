<?php

declare(strict_types=1);

namespace App\Tasks;

use App\DataDefinitions\FieldsDefinitions;
use App\Entity\ArtisanUrl;
use App\Repository\ArtisanUrlRepository;
use App\Service\WebpageSnapshotManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

final class ArtisanUrlInspection
{
    private ArtisanUrlRepository $artisanUrlRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private WebpageSnapshotManager $webpageSnapshotManager,
        private SymfonyStyle $io,
    ) {
        $this->artisanUrlRepository = $entityManager->getRepository(ArtisanUrl::class);
    }

    public function inspect(int $limit): void
    {
        $urls = $this->artisanUrlRepository->getLeastRecentFetched($limit, FieldsDefinitions::NON_INSPECTED_URLS);

        $this->io->progressStart(count($urls));

        foreach ($urls as $url) {
            try {
                $this->webpageSnapshotManager->get($url, true, false);
            } catch (ExceptionInterface) {
                // Ignore - failure has been recorded
            }

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();
    }
}
