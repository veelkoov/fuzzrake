<?php

declare(strict_types=1);

namespace App\Tasks;

use App\Entity\Artisan;
use App\Entity\Event;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Utils\Artisan\Fields;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Tracking\AnalysisResult;
use App\Utils\Tracking\CommissionsStatusParser;
use App\Utils\Tracking\Status;
use App\Utils\Tracking\TrackerException;
use App\Utils\Web\Fetchable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

final class CommissionsStatusesUpdate
{
    private ArtisanRepository $artisanRepository;

    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private WebpageSnapshotManager $snapshots,
        private CommissionsStatusParser $commissionsStatusParser,
        private SymfonyStyle $io,
        private bool $refetch,
        private bool $dryRun,
    ) {
        $this->artisanRepository = $entityManager->getRepository(Artisan::class);
        $this->io->getFormatter()->setStyle('open', new OutputFormatterStyle('green'));
        $this->io->getFormatter()->setStyle('closed', new OutputFormatterStyle('red'));
        $this->io->getFormatter()->setStyle('context', new OutputFormatterStyle('blue'));
    }

    public function updateAll()
    {
        $urls = $this->getCstUrls($this->getTrackedArtisans());

        $this->snapshots->prefetchUrls($urls, $this->refetch, $this->io);

        foreach ($urls as $url) {
            $this->performUpdate($url);
        }
    }

    private function performUpdate(Fetchable $url): void
    {
        $artisan = $url->getArtisan();

        [$datetimeRetrieved, $analysisResult] = $this->analyzeStatus($url);

        $this->reportStatusChange($artisan, $analysisResult);

        if (!$this->dryRun) {
            $artisan->getCommissionsStatus()
                ->setStatus($analysisResult->getStatus())
                ->setLastChecked($datetimeRetrieved);
        }
    }

    private function analyzeStatus(Fetchable $url): array
    {
        $datetimeRetrieved = null;
        $analysisResult = null;

        try {
            $webpageSnapshot = $this->snapshots->get($url, false, false);

            $datetimeRetrieved = $webpageSnapshot->getRetrievedAt();
            $analysisResult = $this->commissionsStatusParser->analyseStatus($webpageSnapshot);
        } catch (TrackerException | InvalidArgumentException $exception) {
            $this->logger->warning($exception->getMessage());
        } catch (ExceptionInterface) {
            /* Was recorded & logged, proceed with "UNKNOWN" */
        }

        return [$datetimeRetrieved ?? DateTimeUtils::getNowUtc(), $analysisResult ?? AnalysisResult::getNull()];
    }

    private function canAutoUpdate(Artisan $artisan): bool
    {
        return !empty($artisan->getCstUrl());
    }

    private function reportStatusChange(Artisan $artisan, AnalysisResult $analysisResult): void
    {
        if ($artisan->getCommissionsStatus()->getStatus() !== $analysisResult->getStatus()) {
            $oldStatusText = Status::text($artisan->getCommissionsStatus()->getStatus());
            $newStatusText = Status::text($analysisResult->getStatus());

            $this->io->caution("{$artisan->getName()} ( {$artisan->getCstUrl()} ): {$analysisResult->explanation()}, $oldStatusText ---> $newStatusText");
        } elseif ($analysisResult->hasFailed()) {
            $this->io->note("{$artisan->getName()} ( {$artisan->getCstUrl()} ): {$analysisResult->explanation()}");
        } else {
            return;
        }

        if ($analysisResult->openMatched()) {
            $this->io->text("Matched OPEN ({$analysisResult->getOpenRegexpId()}): ".
                "<context>{$analysisResult->getOpenStrContext()->getBefore()}</>".
                "<open>{$analysisResult->getOpenStrContext()->getSubject()}</>".
                "<context>{$analysisResult->getOpenStrContext()->getAfter()}</>");
        }

        if ($analysisResult->closedMatched()) {
            $this->io->text("Matched CLOSED ({$analysisResult->getClosedRegexpId()}): ".
                "<context>{$analysisResult->getClosedStrContext()->getBefore()}</>".
                "<closed>{$analysisResult->getClosedStrContext()->getSubject()}</>".
                "<context>{$analysisResult->getClosedStrContext()->getAfter()}</>");
        }

        if ($artisan->getCommissionsStatus()->getStatus() !== $analysisResult->getStatus()) {
            $this->entityManager->persist(new Event($artisan->getCstUrl(), $artisan->getName(),
                $artisan->getCommissionsStatus()->getStatus(), $analysisResult));
        }
    }

    /**
     * @return Artisan[]
     */
    private function getTrackedArtisans(): array
    {
        return array_filter($this->artisanRepository->findAll(), fn (Artisan $artisan): bool => $this->canAutoUpdate($artisan));
    }

    /**
     * @param Artisan[]
     *
     * @return Fetchable[]
     */
    private function getCstUrls(array $artisans): array
    {
        return array_map(fn (Artisan $artisan): Fetchable => $artisan->getSingleUrlObject(Fields::URL_CST), $artisans);
    }
}
