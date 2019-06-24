<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Entity\Event;
use App\Repository\ArtisanRepository;
use App\Utils\Regexp\RegexpFailure;
use App\Utils\Tracking\AnalysisResult;
use App\Utils\Tracking\CommissionsStatusParser;
use App\Utils\Tracking\TrackerException;
use App\Utils\DateTimeUtils;
use App\Utils\Tracking\Status;
use App\Utils\Web\UrlFetcherException;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Symfony\Component\Console\Style\StyleInterface;

class CommissionStatusUpdateService
{
    /**
     * @var ArtisanRepository
     */
    private $artisanRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var WebpageSnapshotManager
     */
    private $snapshots;

    /**
     * @var StyleInterface
     */
    private $style;

    /**
     * @var CommissionsStatusParser
     */
    private $parser;

    public function __construct(ObjectManager $objectManager, WebpageSnapshotManager $snapshots)
    {
        $this->objectManager = $objectManager;
        $this->artisanRepository = $objectManager->getRepository(Artisan::class);
        $this->snapshots = $snapshots;
        $this->parser = new CommissionsStatusParser();
    }

    /**
     * @param StyleInterface $style
     * @param bool           $refresh
     * @param bool           $dryRun
     *
     * @throws RegexpFailure
     */
    public function updateAll(StyleInterface $style, bool $refresh, bool $dryRun)
    {
        $this->style = $style;

        $artisans = $this->getArtisans();
        $this->prefetchStatusWebpages($artisans, $refresh);
        $this->updateArtisans($artisans);

        if (!$dryRun) {
            $this->objectManager->flush();
        }
    }

    private function updateArtisans(array $artisans): void
    {
        foreach ($artisans as $artisan) {
            if ($this->canAutoUpdate($artisan)) {
                try {
                    $this->updateArtisan($artisan);
                } catch (Exception $exception) {
                    $this->style->error("Failed: {$artisan->getName()} ( {$artisan->getCommissionsQuotesCheckUrl()} )");
                    $this->style->text($exception);
                }
            }
        }
    }

    /**
     * @param Artisan $artisan
     *
     * @throws Exception
     */
    private function updateArtisan(Artisan $artisan): void
    {
        try {
            $webpageSnapshot = $this->snapshots->get($artisan->getCommissionsQuotesCheckUrl(), $artisan->getName());
            $datetimeRetrieved = $webpageSnapshot->getRetrievedAt();
            $analysisResult = $this->parser->analyseStatus($webpageSnapshot);
        } catch (TrackerException $exception) { // FIXME: actual failure would result in "NONE MATCHES" interpretation
            $datetimeRetrieved = DateTimeUtils::getNowUtc(); // Fallback value
            $analysisResult = new AnalysisResult(null, null);
        }

        $this->reportStatusChange($artisan, $analysisResult);
        $artisan->setAreCommissionsOpen($analysisResult->getStatus());
        $artisan->setCommissionsQuotesLastCheck($datetimeRetrieved);
    }

    private function canAutoUpdate(Artisan $artisan): bool
    {
        return !empty($artisan->getCommissionsQuotesCheckUrl());
    }

    /**
     * @param Artisan        $artisan
     * @param AnalysisResult $analysisResult
     *
     * @throws Exception
     */
    private function reportStatusChange(Artisan $artisan, AnalysisResult $analysisResult) // FIXME
    {
        if ($artisan->getAreCommissionsOpen() !== $analysisResult->getStatus()) {
            $this->style->note("Failed: {$artisan->getName()} ( {$artisan->getCommissionsQuotesCheckUrl()} ): {$exception->getMessage()}");

            $oldStatusText = Status::text($artisan->getAreCommissionsOpen());
            $newStatusText = Status::text($analysisResult->getStatus());
            $checkedUrl = $artisan->getCommissionsQuotesCheckUrl();

            $this->style->caution("{$artisan->getName()} ( {$checkedUrl} ) $oldStatusText ---> $newStatusText");

            $this->objectManager->persist(new Event($checkedUrl, $artisan->getName(),
                $artisan->getAreCommissionsOpen(), $analysisResult->getStatus()));
        }
    }

    /**
     * @param array $artisans
     * @param bool  $refresh
     *
     * @throws RegexpFailure
     */
    private function prefetchStatusWebpages(array $artisans, bool $refresh): void
    {
        if ($refresh) {
            $this->snapshots->clearCache();
        }

        $this->style->progressStart(count($artisans));

        foreach ($artisans as $artisan) {
            if ($this->canAutoUpdate($artisan)) {
                $url = $artisan->getCommissionsQuotesCheckUrl();

                try {
                    $this->snapshots->get($url, $artisan->getName());
                } catch (UrlFetcherException $exception) {
                    $this->style->note("Failed fetching: {$artisan->getName()} ( {$url} ): {$exception->getMessage()}");
                }
            }

            $this->style->progressAdvance();
        }

        $this->style->progressFinish();
    }

    /**
     * @return Artisan[]
     */
    private function getArtisans(): array
    {
        return $this->artisanRepository->findAll();
    }
}
