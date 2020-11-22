<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates\Commissions;

use App\Entity\Artisan;
use App\Entity\ArtisanUrl;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\AnalysisResultInterface;
use App\Tasks\TrackerUpdates\TrackerUpdatesConfig;
use App\Tasks\TrackerUpdates\UpdatesInterface;
use App\Utils\Artisan\Fields;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Tracking\CommissionsStatusParser;
use App\Utils\Tracking\NullMatch;
use App\Utils\Tracking\TrackerException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

final class CommissionsUpdates implements UpdatesInterface
{
    private TrackerUpdatesConfig $config;
    private ArtisanRepository $repository;
    private LoggerInterface $logger;
    private CommissionsStatusParser $parser;
    private WebpageSnapshotManager $snapshots;

    /**
     * @var Artisan[]
     */
    private array $updated;

    public function __construct(TrackerUpdatesConfig $config, ArtisanRepository $repository, LoggerInterface $logger, WebpageSnapshotManager $snapshots)
    {
        $this->repository = $repository;
        $this->config = $config;
        $this->logger = $logger;
        $this->snapshots = $snapshots;
        $this->parser = new CommissionsStatusParser();

        $this->updated = array_filter($this->repository->findAll(), fn (Artisan $artisan): bool => !empty($artisan->getCommissionsUrl()));
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
     */
    public function perform(): array
    {
        return array_map(fn (Artisan $artisan) => $this->performOnSingle($artisan), $this->updated);
    }

    private function performOnSingle(Artisan $artisan): AnalysisResultInterface
    {
        $urls = $artisan->getUrlObjs(Fields::URL_COMMISSIONS);
        $url = array_pop($urls); // FIXME: Handle multiple

        [$datetimeRetrieved, $analysisResult] = $this->analyzeStatus($url);

        $artisan->getCommissionsStatus()
            ->setStatus($analysisResult->getStatus())
            ->setLastChecked($datetimeRetrieved);

        return $analysisResult;
    }

    private function analyzeStatus(ArtisanUrl $url): array
    {
        $datetimeRetrieved = null;
        $analysisResult = null;

        try {
            $webpageSnapshot = $this->snapshots->get($url, false, false);

            $datetimeRetrieved = $webpageSnapshot->getRetrievedAt();
            [$openMatch, $closedMatch] = $this->parser->analyseStatus($webpageSnapshot);
            $analysisResult = new CommissionsAnalysisResult($url->getArtisan(), $openMatch, $closedMatch);
        } catch (TrackerException | InvalidArgumentException $exception) {
            $this->logger->warning($exception->getMessage());
        } catch (ExceptionInterface $exception) {
            /* Was recorded & logged, proceed with "UNKNOWN" */
        }

        if (null === $analysisResult) {
            $analysisResult = new CommissionsAnalysisResult($url->getArtisan(), NullMatch::get(), NullMatch::get());
        }

        return [$datetimeRetrieved ?? DateTimeUtils::getNowUtc(), $analysisResult];
    }
}
