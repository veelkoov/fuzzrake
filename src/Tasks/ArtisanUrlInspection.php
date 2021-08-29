<?php

declare(strict_types=1);

namespace App\Tasks;

use App\DataDefinitions\FieldsDefinitions;
use App\Repository\ArtisanUrlRepository;
use App\Service\WebpageSnapshotManager;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

final class ArtisanUrlInspection
{
    public function __construct(
        private ArtisanUrlRepository $artisanUrlRepository,
        private WebpageSnapshotManager $webpageSnapshotManager,
        private SymfonyStyle $io,
    ) {
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
