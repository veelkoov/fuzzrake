<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\DateTimeUtils;
use App\Utils\Web\Cache;
use App\Utils\Web\UrlFetcher;
use App\Utils\Web\UrlFetcherException;
use App\Utils\Web\WebpageSnapshot;
use App\Utils\Web\WebsiteInfo;
use Exception;

class WebpageSnapshotManager
{
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
     * @throws UrlFetcherException
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
     * @throws Exception
     */
    private function download(string $url, string $ownerName): WebpageSnapshot
    {
        $webpageSnapshot = new WebpageSnapshot($url, $this->fetcher->get($url), DateTimeUtils::getNowUtc(), $ownerName);

        $this->downloadChildren($webpageSnapshot);

        return $webpageSnapshot;
    }

    private function downloadChildren(WebpageSnapshot $webpageSnapshot): void
    {
        if (WebsiteInfo::isWixsite($webpageSnapshot)) {
            $child = $this->fetchWixsiteContents($webpageSnapshot);
        } elseif (WebsiteInfo::isTrello($webpageSnapshot)) {
            $child = $this->fetchTrelloContents($webpageSnapshot);
        } else {
            $child = null;
        }

        if ($child) {
            $webpageSnapshot->addChildren($child);
        }
    }

    private function fetchWixsiteContents(WebpageSnapshot $snapshot): ?WebpageSnapshot // TODO: refactor
    {
        if (0 === preg_match('#"masterPageJsonFileName"\s*:\s*"(?<hash>[a-z0-9_]+).json"#s',
                $snapshot->getContents(), $matches)) {
            return null;
        }

        $hash = $matches['hash'];

        if (0 === preg_match("#<link[^>]* href=\"(?<data_url>https://static.wixstatic.com/sites/(?!$hash)[a-z0-9_]+\.json\.z\?v=\d+)\"[^>]*>#si",
                $snapshot->getContents(), $matches)) {
            return null;
        }

        return $this->get($matches['data_url'], $snapshot->getOwnerName());
    }

    private function fetchTrelloContents(WebpageSnapshot $snapshot): ?WebpageSnapshot // TODO: refactor
    {
        if (0 === preg_match('#^https?://trello.com/b/(?<boardId>[a-zA-Z0-9]+)/#', $snapshot->getUrl(), $matches)) {
            return null;
        }

        $boardId = $matches['boardId'];

        return $this->get("https://trello.com/1/Boards/$boardId?lists=open&list_fields=name&cards=visible&card_attachments=false&card_stickers=false&card_fields=desc%2CdescData%2Cname&card_checklists=none&members=none&member_fields=none&membersInvited=none&membersInvited_fields=none&memberships_orgMemberType=false&checklists=none&organization=false&organization_fields=none%2CdisplayName%2Cdesc%2CdescData%2Cwebsite&organization_tags=false&myPrefs=false&fields=name%2Cdesc%2CdescData", $snapshot->getOwnerName());
    }
}
