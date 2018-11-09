<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Utils\CommissionsStatusParser;
use App\Utils\CommissionsStatusParserException;
use App\Utils\WebpageSnapshot;
use App\Utils\WebsiteInfo;
use DateTime;
use DateTimeZone;
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

    /**
     * @var CommissionsStatusParser
     */
    private $commissionsStatusParser;

    public function __construct(ArtisanRepository $artisanRepository, ObjectManager $objectManager, UrlFetcher $urlFetcher)
    {
        $this->objectManager = $objectManager;
        $this->artisanRepository = $artisanRepository;
        $this->urlFetcher = $urlFetcher;
        $this->commissionsStatusParser = new CommissionsStatusParser();
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
                    $this->style->error("Failed: {$artisan->getName()} ( {$artisan->getCommisionsQuotesCheckUrl()} )");
                    $this->style->text($exception);
                }
            }
        }
    }

    private function updateArtisan(Artisan $artisan): void
    {
        list($status, $datetimeRetrieved) = $this->getCommissionsStatusAndDateTimeChecked($artisan);

        $this->reportStatusChange($artisan, $status);
        $artisan->setAreCommissionsOpen($status);
        $artisan->setCommissionsQuotesLastCheck($datetimeRetrieved);
    }

    private function canAutoUpdate(Artisan $artisan): bool
    {
        return !empty($artisan->getCommisionsQuotesCheckUrl());
    }

    private function reportStatusChange(Artisan $artisan, ?bool $newStatus)
    {
        $prefix = "{$artisan->getName()} ( {$artisan->getCommisionsQuotesCheckUrl()} ) commissions are now";

        if (true !== $artisan->getAreCommissionsOpen() && true === $newStatus) {
            $this->style->caution("$prefix OPEN");
        }

        if (false !== $artisan->getAreCommissionsOpen() && false === $newStatus) {
            $this->style->caution("$prefix CLOSED");
        }

        if (null !== $artisan->getAreCommissionsOpen() && null === $newStatus) {
            $this->style->caution("$prefix UNKNOWN");
        }
    }

    /**
     * @param array $artisans
     * @param bool  $refresh
     */
    private function prefetchStatusWebpages(array $artisans, bool $refresh): void
    {
        if ($refresh) {
            $this->urlFetcher->clearCache();
        }

        $this->style->progressStart(count($artisans));

        foreach ($artisans as $artisan) {
            if ($this->canAutoUpdate($artisan)) {
                $url = $artisan->getCommisionsQuotesCheckUrl();

                try {
                    $this->fetchWebpageContents($url);
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
     * @param string $url
     *
     * @return WebpageSnapshot
     *
     * @throws UrlFetcherException
     */
    private function fetchWebpageContents(string $url): WebpageSnapshot
    {
        $webpageSnapshot = $this->urlFetcher->fetchWebpage($url);

        if (WebsiteInfo::isWixsite($webpageSnapshot)) {
            $webpageSnapshot = $this->fetchWixsiteContents($webpageSnapshot);
        } elseif (WebsiteInfo::isTrello($webpageSnapshot)) {
            $webpageSnapshot = $this->fetchTrelloContents($webpageSnapshot);
        }

        return $webpageSnapshot;
    }

    /**
     * @param WebpageSnapshot $webpageSnapshot
     *
     * @return WebpageSnapshot
     *
     * @throws UrlFetcherException
     */
    private function fetchWixsiteContents(WebpageSnapshot $webpageSnapshot): WebpageSnapshot
    {
        preg_match('#"masterPageJsonFileName"\s*:\s*"(?<hash>[a-z0-9_]+).json"#s',
            $webpageSnapshot->getContents(), $matches);

        $hash = $matches['hash'];

        preg_match("#<link[^>]* href=\"(?<data_url>https://static.wixstatic.com/sites/(?!$hash)[a-z0-9_]+\.json\.z\?v=\d+)\"[^>]*>#si",
            $webpageSnapshot->getContents(), $matches);

        return $this->urlFetcher->fetchWebpage($matches['data_url']);
    }

    /**
     * @param WebpageSnapshot $webpageSnapshot
     *
     * @return WebpageSnapshot
     *
     * @throws UrlFetcherException
     */
    private function fetchTrelloContents(WebpageSnapshot $webpageSnapshot): WebpageSnapshot
    {
        preg_match('#^https?://trello.com/b/(?<boardId>[a-zA-Z0-9]+)/#', $webpageSnapshot->getUrl(), $matches);

        $boardId = $matches['boardId'];

        return $this->urlFetcher->fetchWebpage("https://trello.com/1/Boards/$boardId?lists=open&list_fields=name&cards=visible&card_attachments=false&card_stickers=false&card_fields=desc%2CdescData%2Cname&card_checklists=none&members=none&member_fields=none&membersInvited=none&membersInvited_fields=none&memberships_orgMemberType=false&checklists=none&organization=false&organization_fields=none%2CdisplayName%2Cdesc%2CdescData%2Cwebsite&organization_tags=false&myPrefs=false&fields=name%2Cdesc%2CdescData");
    }

    private function guessFilterFromUrl(string $url): string
    {
        if (preg_match('/#(?<profile>.+)$/', $url, $zapałki)) {
            return $zapałki['profile'];
        } else {
            return '';
        }
    }

    /**
     * @param Artisan $artisan
     *
     * @return array
     */
    private function getCommissionsStatusAndDateTimeChecked(Artisan $artisan): array
    {
        // FIXME: UTC for unknown is CE(S)T instead
        $url = $artisan->getCommisionsQuotesCheckUrl();
        $datetimeRetrieved = null;

        try {
            $webpageSnapshot = $this->fetchWebpageContents($url);
            $datetimeRetrieved = $webpageSnapshot->getDatetimeRetrieved();
            $status = $this->commissionsStatusParser->areCommissionsOpen($webpageSnapshot->getContents(),
                $artisan->getName(), $this->guessFilterFromUrl($url));
        } catch (UrlFetcherException | CommissionsStatusParserException $exception) {
            $this->style->note("Failed: {$artisan->getName()} ( {$url} ): {$exception->getMessage()}");
            $status = null;
        }

        return [$status, $datetimeRetrieved ?: $this->getNowUtc()];
    }

    /**
     * @return DateTime
     */
    private function getNowUtc(): DateTime
    {
        return new DateTime('now', new DateTimeZone('UTC'));
    }
}
