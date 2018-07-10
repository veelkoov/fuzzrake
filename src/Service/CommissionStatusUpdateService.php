<?php

namespace App\Service;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\CommissionsOpenParser;
use Doctrine\Common\Persistence\ObjectManager;
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

        if ($refresh) {
            $this->urlFetcher->clearCache();
        }

        $this->updateArtisans();

        if (!$dryRun) {
            $this->objectManager->flush();
        }
    }

    private function updateArtisans(): void
    {
        $artisans = $this->artisanRepository->findAll();
        $this->style->progressStart(count($artisans));

        foreach ($artisans as $artisan) {
            if ($this->canAutoUpdate($artisan)) {
                try {
                    $this->updateArtisan($artisan);
                } catch (\Exception $exception) {
                    $this->style->error("Failed updating: {$artisan->getName()} with {$artisan->getCommisionsQuotesCheckUrl()}");
                    $this->style->text($exception);
                }

                $this->style->progressAdvance();
            }
        }

        $this->style->progressFinish();
    }

    private function updateArtisan(Artisan $artisan): void
    {
        $webpageContents = $this->urlFetcher->fetchWebPage($artisan->getCommisionsQuotesCheckUrl());
        $status = CommissionsOpenParser::areCommissionsOpen($webpageContents);

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
            $this->style->note($artisan->getName() . ' commissions are now OPEN');
        }

        if ($artisan->getAreCommissionsOpen() !== false && $newStatus === false) {
            $this->style->note($artisan->getName() . ' commissions are now CLOSED');
        }

        if ($newStatus === null) {
            if ($artisan->getAreCommissionsOpen() === null) {
                $this->style->note($artisan->getName() . ' commissions are UNKNOWN');
            } else {
                $this->style->caution($artisan->getName() . ' commissions are now UNKNOWN');
            }
        }
    }
}
