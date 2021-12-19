<?php

declare(strict_types=1);

namespace App\Tasks;

use App\DataDefinitions\Fields\Fields;
use App\Repository\ArtisanUrlRepository;
use App\Service\WebpageSnapshotManager;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

final class ArtisanUrlInspection
{
    public function __construct(
        private readonly ArtisanUrlRepository $artisanUrlRepository,
        private readonly WebpageSnapshotManager $webpageSnapshotManager,
        private readonly SymfonyStyle $io,
    ) {
    }

    public function inspect(int $limit): void
    {
        $urls = $this->artisanUrlRepository->getLeastRecentFetched($limit, Fields::nonInspectedUrls());

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
