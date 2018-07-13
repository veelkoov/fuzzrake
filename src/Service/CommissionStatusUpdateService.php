<?php

namespace App\Service;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\CommissionsStatusParser;
use App\Utils\CommissionsStatusParserException;
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
     * @var UrlFetcher
     */
    private $urlFetcher;

    /**
     * @var StyleInterface
     */
    private $style;

    public function __construct(ArtisanRepository $artisanRepository, ObjectManager $objectManager, UrlFetcher $urlFetcher)
    {
        $this->objectManager = $objectManager;
        $this->artisanRepository = $artisanRepository;
        $this->urlFetcher = $urlFetcher;
    }

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
                    $this->style->error("Failed: {$artisan->getName()} ({$artisan->getCommisionsQuotesCheckUrl()})");
                    $this->style->text($exception);
                }
            }
        }
    }

    private function updateArtisan(Artisan $artisan): void
    {
        $webpageContents = $this->urlFetcher->fetchWebPage($artisan->getCommisionsQuotesCheckUrl());

        try {
            $status = CommissionsStatusParser::areCommissionsOpen($webpageContents);
        } catch (CommissionsStatusParserException $exception) {
            $this->style->note("Failed: {$artisan->getName()} ({$artisan->getCommisionsQuotesCheckUrl()}): {$exception->getMessage()}");
            $status = null;
        }

        $this->reportStatusChange($artisan, $status);
        $artisan->setAreCommissionsOpen($status);
    }

    private function canAutoUpdate(Artisan $artisan): bool
    {
        return !empty($artisan->getCommisionsQuotesCheckUrl());
    }

    private function reportStatusChange(Artisan $artisan, ?bool $newStatus)
    {
        if ($artisan->getAreCommissionsOpen() !== true && $newStatus === true) {
            $this->style->caution($artisan->getName() . ' commissions are now OPEN');
        }

        if ($artisan->getAreCommissionsOpen() !== false && $newStatus === false) {
            $this->style->caution($artisan->getName() . ' commissions are now CLOSED');
        }

        if ($artisan->getAreCommissionsOpen() !== null && $newStatus === null) {
            $this->style->caution($artisan->getName() . ' commissions are now UNKNOWN');
        }
    }

    private function prefetchStatusWebpages(array $artisans, bool $refresh): void
    {
        if ($refresh) {
            $this->urlFetcher->clearCache();
        }

        $this->style->progressStart(count($artisans));

        foreach ($artisans as $artisan) {
            if ($this->canAutoUpdate($artisan)) {
                $this->urlFetcher->fetchWebPage($artisan->getCommisionsQuotesCheckUrl());
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
