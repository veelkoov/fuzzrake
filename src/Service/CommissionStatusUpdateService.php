<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Entity\Event;
use App\Repository\ArtisanRepository;
use App\Utils\CommissionsStatusParser;
use App\Utils\CommissionsStatusParserException;
use App\Utils\DateTimeUtils;
use App\Utils\Web\UrlFetcherException;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Symfony\Component\Console\Style\StyleInterface;

class CommissionStatusUpdateService
{
    const STATUS_UNKNOWN = 'UNKNOWN';
    const STATUS_OPEN = 'OPEN';
    const STATUS_CLOSED = 'CLOSED';

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
        list($status, $datetimeRetrieved) = $this->getCommissionsStatusAndDateTimeChecked($artisan);

        $this->reportStatusChange($artisan, $status);
        $artisan->setAreCommissionsOpen($status);
        $artisan->setCommissionsQuotesLastCheck($datetimeRetrieved);
    }

    private function canAutoUpdate(Artisan $artisan): bool
    {
        return !empty($artisan->getCommissionsQuotesCheckUrl());
    }

    private function reportStatusChange(Artisan $artisan, ?bool $newStatus)
    {
        if ($artisan->getAreCommissionsOpen() !== $newStatus) {
            $newStatusText = $this->textStatus($newStatus);
            $oldStatusText = $this->textStatus($artisan->getAreCommissionsOpen());

            $this->style->caution("{$artisan->getName()} ( {$artisan->getCommissionsQuotesCheckUrl()} ) $oldStatusText ---> $newStatusText");

            $this->objectManager->persist($this->getStatusChangeEvent($artisan->getName(), $artisan->getCommissionsQuotesCheckUrl(), $oldStatusText, $newStatusText));
        }
    }

    private function textStatus(?bool $status): string
    {
        if (null === $status) {
            return self::STATUS_UNKNOWN;
        }

        return $status ? self::STATUS_OPEN : self::STATUS_CLOSED;
    }

    /**
     * @param array $artisans
     * @param bool  $refresh
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

    /**
     * @param Artisan $artisan
     *
     * @return array
     *
     * @throws Exception
     */
    private function getCommissionsStatusAndDateTimeChecked(Artisan $artisan): array
    {
        // FIXME: UTC for unknown is CE(S)T instead
        $url = $artisan->getCommissionsQuotesCheckUrl();
        $datetimeRetrieved = null;

        try {
            $webpageSnapshot = $this->snapshots->get($url, $artisan->getName());
            $datetimeRetrieved = $webpageSnapshot->getRetrievedAt();
            $status = $this->parser->areCommissionsOpen($webpageSnapshot);
        } catch (UrlFetcherException | CommissionsStatusParserException $exception) {
            $this->style->note("Failed: {$artisan->getName()} ( {$url} ): {$exception->getMessage()}");
            $status = null;
        }

        return [$status, $datetimeRetrieved ?: DateTimeUtils::getNowUtc()];
    }

    private function getStatusChangeEvent(string $name, string $url, string $oldStatus, string $newStatus): Event
    {
        if (self::STATUS_UNKNOWN === $newStatus) {
            return new Event("The software failed to interpret new commission status based on the contents of: $url . $name commission status is now $newStatus (was $oldStatus).");
        }

        return new Event("Based on the contents of: $url , $name commission status changed to $newStatus (was $oldStatus).");
    }
}
