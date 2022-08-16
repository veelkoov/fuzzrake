<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\Traits\UtilityClass;
use App\Utils\UnbelievableRuntimeException;
use App\Utils\Web\WebpageSnapshot\Snapshot;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;

final class WebsiteInfo
{
    use UtilityClass;

    private const FA_URL_SEARCH_STRING = 'furaffinity.net/';
    private const FA_CONTENTS_SEARCH_STRING = 'fur affinity [dot] net</title>';
    private const FA_JOUNRAL_CONTENTS_SEARCH_STRING = 'journal -- fur affinity [dot] net</title>';
    private const FA_ACCOUNT_DISABLED_CONTENTS_SEARCH_STRING = '<title>Account disabled. -- Fur Affinity [dot] net</title>';
    private const FA_ACCOUNT_LOGIN_REQUIRED_CONTENTS_SEARCH_STRING = '<p class="link-override">The owner of this page has elected to make it available to registered users only.';
    private const FA_USER_NOT_FOUND_CONTENTS_SEARCH_STRING = 'This user cannot be found.';
    private const FA_SYSTEM_ERROR_CONTENTS_SEARCH_STRING = '<title>System Error</title>';

    private const WIXSITE_CONTENTS_REGEXP = '<meta\s+name="generator"\s+content="Wix\.com Website Builder"\s*/?>';
    private const TWITTER_CONTENTS_SEARCH_STRING = 'Twitter</title>';
    private const INSTAGRAM_CONTENTS_REGEXP = 'Instagram photos and videos\s*</title>';

    private const TRELLO_BOARD_URL_REGEXP = '^https?://trello.com/b/(?<boardId>[a-zA-Z0-9]+)/';
    private const WIXSITE_CHILDREN_REGEXP = '<link[^>]* href="(?<data_url>https://static.wixstatic.com/sites/[a-z0-9_]+\.json\.z\?v=\d+)"[^>]*>';

    public static function isWixsite(Snapshot $webpageSnapshot): bool
    {
        if (false !== stripos($webpageSnapshot->url, '.wixsite.com/')) {
            return true;
        }

        return pattern(self::WIXSITE_CONTENTS_REGEXP, 'si')->test($webpageSnapshot->contents);
    }

    public static function isTrello(Snapshot $webpageSnapshot): bool
    {
        return false !== stripos($webpageSnapshot->url, '//trello.com/');
    }

    public static function isFurAffinity(?string $url, ?string $webpageContents): bool
    {
        if (null !== $url) {
            return false !== stripos($url, self::FA_URL_SEARCH_STRING);
        }

        if (null !== $webpageContents) {
            return false !== stripos($webpageContents, self::FA_CONTENTS_SEARCH_STRING);
        }

        return false;
    }

    public static function isFurAffinityUserProfile(string $url, string $webpageContents): bool
    {
        if (!self::isFurAffinity($url, $webpageContents)) {
            return false;
        }

        return false === stripos($webpageContents, self::FA_JOUNRAL_CONTENTS_SEARCH_STRING);
    }

    public static function isTwitter(string $websiteContents): bool
    {
        return false !== stripos($websiteContents, self::TWITTER_CONTENTS_SEARCH_STRING);
    }

    public static function isInstagram(string $webpageContents): bool
    {
        return pattern(self::INSTAGRAM_CONTENTS_REGEXP, 'si')->test($webpageContents);
    }

    public static function getTrelloBoardDataUrl(string $boardId): string
    {
        return "https://trello.com/1/Boards/$boardId?lists=open&list_fields=name&cards=visible&card_attachments=false&card_stickers=false&card_fields=desc%2CdescData%2Cname&card_checklists=none&members=none&member_fields=none&membersInvited=none&membersInvited_fields=none&memberships_orgMemberType=false&checklists=none&organization=false&organization_fields=none%2CdisplayName%2Cdesc%2CdescData%2Cwebsite&organization_tags=false&myPrefs=false&fields=name%2Cdesc%2CdescData";
    }

    /**
     * @return string[]
     */
    public static function getChildrenUrls(Snapshot $webpageSnapshot): array
    {
        if (WebsiteInfo::isWixsite($webpageSnapshot)) {
            return self::getWixsiteDependencyUrls($webpageSnapshot);
        } elseif (WebsiteInfo::isTrello($webpageSnapshot)) {
            return self::getTrelloDependencyUrls($webpageSnapshot);
        } else {
            return [];
        }
    }

    /**
     * @return string[]
     */
    private static function getWixsiteDependencyUrls(Snapshot $webpageSnapshot): array
    {
        return pattern(WebsiteInfo::WIXSITE_CHILDREN_REGEXP, 'si')
            ->match($webpageSnapshot->contents)
            ->group('data_url')
            ->all();
    }

    /**
     * @return string[]
     */
    private static function getTrelloDependencyUrls(Snapshot $webpageSnapshot): array
    {
        return pattern(WebsiteInfo::TRELLO_BOARD_URL_REGEXP)
            ->match($webpageSnapshot->url)
            ->findFirst(function (Detail $detail): array {
                try {
                    return [WebsiteInfo::getTrelloBoardDataUrl($detail->get('boardId'))];
                } catch (NonexistentGroupException $e) {
                    throw new UnbelievableRuntimeException($e);
                }
            })
            ->orReturn([]);
    }

    public static function getLatentCode(string $url, string $content): ?int
    {
        if (self::isFurAffinity($url, $content)) {
            if (str_contains($content, self::FA_ACCOUNT_DISABLED_CONTENTS_SEARCH_STRING)) {
                return 410;
            } elseif (false !== stripos($content, self::FA_ACCOUNT_LOGIN_REQUIRED_CONTENTS_SEARCH_STRING)) {
                return 401;
            } elseif (false !== stripos($content, self::FA_USER_NOT_FOUND_CONTENTS_SEARCH_STRING)
                && false !== stripos($content, self::FA_SYSTEM_ERROR_CONTENTS_SEARCH_STRING)) {
                return 404;
            }
        }

        return null;
    }
}
