<?php

namespace App\Service;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\CommissionsOpenParser;
use App\Utils\UrlFetcher;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Filesystem\Filesystem;

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
     * @var string
     */
    private $snapshotsDirPath;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var StyleInterface
     */
    private $style;

    /**
     * @var array
     */
    private $lastRequests;

    const DELAY_FOR_HOST = 5;

    public function __construct(ArtisanRepository $artisanRepository, ObjectManager $objectManager, string $projectDir)
    {
        $this->snapshotsDirPath = "$projectDir/var/snapshots/";
        $this->fs = new Filesystem();
        $this->fs->mkdir($this->snapshotsDirPath);

        $this->objectManager = $objectManager;
        $this->artisanRepository = $artisanRepository;
        $this->urlFetcher = new UrlFetcher();
    }

    public function updateAll(StyleInterface $style)
    {
        $this->style = $style;
        $this->lastRequests = [];

        $artisans = $this->artisanRepository->findAll();
        $this->style->progressStart(count($artisans));

        foreach ($artisans as $artisan) {
            if ($artisan->getCommisionsQuotesCheckUrl()) {
                try {
                    $this->updateArtisan($artisan);
                } catch (\Exception $exception) {
                    $style->error($exception);
                }

                $this->style->progressAdvance();
            }
        }

        $this->objectManager->flush();
        $this->style->progressFinish();
    }

    private function updateArtisan(Artisan $artisan)
    {
//        $this->delayForHost($artisan->getCommisionsQuotesCheckUrl());

//        $this->fs->dumpFile($this->snapshotsDirPath . $artisan->getId() . '.html',
//            $this->urlFetcher->fetchWebPage($artisan->getCommisionsQuotesCheckUrl()));

        $webpageContents = file_get_contents($this->snapshotsDirPath . $artisan->getId() . '.html');
        $status = CommissionsOpenParser::areCommissionsOpen($webpageContents);

        if ($status === true) {
            $this->style->note($artisan->getName() . ' commissions OPEN');
        } elseif ($status === false) {
            $this->style->note($artisan->getName() . ' commissions CLOSED');
        } else {
            $this->style->warning($artisan->getName() . ' commissions UNKNOWN');
        }

        $this->lastRequests[$artisan->getCommisionsQuotesCheckUrl()] = time();
    }

    private function delayForHost($getCommisionsQuotesCheckUrl)
    {
        $host = parse_url($getCommisionsQuotesCheckUrl, PHP_URL_HOST);

        if (array_key_exists($host, $this->lastRequests)) {
            $this->waitUntil($this->lastRequests[$host], self::DELAY_FOR_HOST);
        }
    }

    private function waitUntil($basetime, $delay): void
    {
        $secondsToWait = $basetime + $delay - time();

        if ($secondsToWait > 0) {
            sleep($secondsToWait);
        }
    }
}
