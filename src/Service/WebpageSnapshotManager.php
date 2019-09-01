<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\DateTimeUtils;
use App\Utils\Regexp\Utils as Regexp;
use App\Utils\Web\Cache;
use App\Utils\Web\UrlFetcher;
use App\Utils\Web\UrlFetcherException;
use App\Utils\Web\WebpageSnapshot;
use App\Utils\Web\WebsiteInfo;

class WebpageSnapshotManager
{
    const WIXSITE_CHILDREN_REGEXP = "#<link[^>]* href=\"(?<data_url>https://static.wixstatic.com/sites/[a-z0-9_]+\.json\.z\?v=\d+)\"[^>]*>#si";

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var UrlFetcher
     */
    private $fetcher;

    public function __construct(string $projectDir)
    {
        $this->cache = new Cache("$projectDir/var/snapshots");
        $this->fetcher = new UrlFetcher();
    }

    public function clearCache(): void
    {
        $this->cache->clear();
    }

    /**
     * @param string $url
     * @param string $ownerName
     *
     * @return WebpageSnapshot
     *
     * @throws UrlFetcherException from inside download()
     */
    public function get(string $url, string $ownerName): WebpageSnapshot
    {
        return $this->cache->getOrSet($url, function () use ($url, $ownerName) {
            return $this->download($url, $ownerName);
        });
    }

    /**
     * @param string $url
     * @param string $ownerName
     *
     * @return WebpageSnapshot
     *
     * @throws UrlFetcherException
     */
    private function download(string $url, string $ownerName): WebpageSnapshot
    {
        $webpageSnapshot = new WebpageSnapshot($url, $this->fetcher->get($url), DateTimeUtils::getNowUtc(), $ownerName);

        $this->downloadChildren($webpageSnapshot);

        return $webpageSnapshot;
    }

    /**
     * @param WebpageSnapshot $webpageSnapshot
     *
     * @throws UrlFetcherException
     */
    private function downloadChildren(WebpageSnapshot $webpageSnapshot): void
    {
        if (WebsiteInfo::isWixsite($webpageSnapshot)) {
            $this->fetchWixsiteContents($webpageSnapshot);
        } elseif (WebsiteInfo::isTrello($webpageSnapshot)) {
            $this->fetchTrelloContents($webpageSnapshot);
        }
    }

    /**
     * @param WebpageSnapshot $snapshot
     *
     * @throws UrlFetcherException
     */
    private function fetchWixsiteContents(WebpageSnapshot $snapshot): void
    {
        if (Regexp::matchAll(self::WIXSITE_CHILDREN_REGEXP, $snapshot->getContents(), $matches)) {
            foreach ($matches['data_url'] as $dataUrl) {
                $snapshot->addChildren($this->get($dataUrl, $snapshot->getOwnerName()));
            }
        }
    }

    /**
     * @param WebpageSnapshot $snapshot
     *
     * @throws UrlFetcherException
     */
    private function fetchTrelloContents(WebpageSnapshot $snapshot): void // TODO: refactor
    {
        if (!Regexp::match('#^https?://trello.com/b/(?<boardId>[a-zA-Z0-9]+)/#', $snapshot->getUrl(), $matches)) {
            return;
        }

        $snapshot->addChildren($this->get("https://trello.com/1/Boards/{$matches['boardId']}?lists=open&list_fields=name&cards=visible&card_attachments=false&card_stickers=false&card_fields=desc%2CdescData%2Cname&card_checklists=none&members=none&member_fields=none&membersInvited=none&membersInvited_fields=none&memberships_orgMemberType=false&checklists=none&organization=false&organization_fields=none%2CdisplayName%2Cdesc%2CdescData%2Cwebsite&organization_tags=false&myPrefs=false&fields=name%2Cdesc%2CdescData", $snapshot->getOwnerName()));
    }
}
