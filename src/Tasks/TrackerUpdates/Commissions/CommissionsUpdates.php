<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates\Commissions;

use App\Entity\Artisan;
use App\Entity\ArtisanCommissionsStatus;
use App\Entity\ArtisanUrl;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\AnalysisResultInterface;
use App\Tasks\TrackerUpdates\TrackerUpdatesConfig;
use App\Tasks\TrackerUpdates\UpdatesInterface;
use App\Utils\Artisan\Fields;
use App\Utils\Tracking\CommissionsStatusParser;
use App\Utils\Tracking\TrackerException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

final class CommissionsUpdates implements UpdatesInterface
{
    /**
     * @var Artisan[]
     */
    private array $updated;

    public function __construct(
        private TrackerUpdatesConfig $config,
        private ArtisanRepository $repository,
        private LoggerInterface $logger,
        private WebpageSnapshotManager $snapshots,
        private CommissionsStatusParser $parser,
    ) {
        $this->updated = array_filter($this->repository->findAll(), fn ($artisan) => !empty($artisan->getCommissionsUrl()));
    }

    /**
     * @return ArtisanUrl[]
     */
    public function getUrlsToPrefetch(): array
    {
        return array_merge(...array_map(fn (Artisan $artisan): array => $artisan->getUrlObjs(Fields::URL_COMMISSIONS), $this->updated));
    }

    /**
     * @return AnalysisResultInterface[]
     *
     * @throws TrackerException
     */
    public function perform(): array
    {
        return array_map(fn (Artisan $artisan) => $this->performOnArtisan($artisan), $this->updated);
    }

    /**
     * @throws TrackerException
     */
    private function performOnArtisan(Artisan $artisan): AnalysisResultInterface
    {
        $result = new CommissionsAnalysisResult($artisan);
        $lastCsUpdate = null;

        $urls = $artisan->getUrlObjs(Fields::URL_COMMISSIONS);
        foreach ($urls as $url) {
            $lastCsUpdate = $url->getState()->getLastRequest();

            try {
                $result->addAcses($this->extractArtisanCommissionsStatuses($url));
            } catch (ExceptionInterface) {
                // TODO: Handle partial?
            }
        }

        $artisan->getVolatileData()->setLastCsUpdate($lastCsUpdate);

        return $result;
    }

    /**
     * @return ArtisanCommissionsStatus[]
     *
     * @throws ExceptionInterface|TrackerException
     */
    private function extractArtisanCommissionsStatuses(ArtisanUrl $url): array
    {
        $webpageSnapshot = $this->snapshots->get($url, false, false);
        $result = [];

        foreach ($this->parser->getOfferStatusPatterns($webpageSnapshot) as $status) {
            $result[] = $status->setArtisan($url->getArtisan());
        }

        return $result;
    }
}
