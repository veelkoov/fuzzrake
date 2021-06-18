<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates\Commissions;

use App\Entity\Artisan;
use App\Entity\ArtisanCommissionsStatus;
use App\Entity\ArtisanUrl;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\ArtisanUpdatesInterface;
use App\Tasks\TrackerUpdates\TrackerTaskInterface;
use App\Utils\Artisan\Fields;
use App\Utils\Tracking\CommissionsStatusParser;
use App\Utils\Tracking\OfferStatus;
use App\Utils\Tracking\TrackerException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class CommissionsTrackerTask implements TrackerTaskInterface
{
    /**
     * @var Artisan[]
     */
    private array $updated;

    public function __construct(
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
     * @return ArtisanUpdatesInterface[]
     */
    public function getUpdates(): array
    {
        return array_map(fn (Artisan $artisan) => $this->getUpdatesFor($artisan), $this->updated);
    }

    private function getUpdatesFor(Artisan $artisan): ArtisanUpdatesInterface
    {
        $result = new CommissionsArtisanUpdates($artisan);

        $lastCsUpdate = null;

        $urls = $artisan->getUrlObjs(Fields::URL_COMMISSIONS);
        foreach ($urls as $url) {
            $lastCsUpdate = $url->getState()->getLastRequest();

            try {
                $result->addAcses($this->extractArtisanCommissionsStatuses($url));
            } catch (ExceptionInterface | TrackerException) {
                // TODO: Record information about failed webpage analysis. See #57
            }
        }

        $artisan->getVolatileData()->setLastCsUpdate($lastCsUpdate);

        return $result;
    }

    /**
     * @return ArtisanCommissionsStatus[]
     *
     * @throws TrackerException
     * @throws ExceptionInterface
     */
    private function extractArtisanCommissionsStatuses(ArtisanUrl $url): array
    {
        $webpageSnapshot = $this->snapshots->get($url, false, false);

        return array_map(function (OfferStatus $match) use ($url): ArtisanCommissionsStatus {
            return (new ArtisanCommissionsStatus())
                ->setIsOpen($match->getStatus())
                ->setOffer($match->getOffer())
                ->setArtisan($url->getArtisan());
        }, $this->parser->getCommissionsStatuses($webpageSnapshot));
    }
}
