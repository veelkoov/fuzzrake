<?php

declare(strict_types=1);

namespace App\Tracking\Web;

use App\Tracking\Web\WebpageSnapshot\Snapshot;
use App\Utils\UnbelievableRuntimeException;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Detail;
use TRegx\CleanRegex\Pattern;

use function Psl\Str\contains_ci;

class WebsiteInfo
{
    private const FA_ACCOUNT_DISABLED_CONTENTS_SEARCH_STRING = '<title>Account disabled. -- Fur Affinity [dot] net</title>';
    private const FA_ACCOUNT_LOGIN_REQUIRED_CONTENTS_SEARCH_STRING = '<p class="link-override">The owner of this page has elected to make it available to registered users only.';
    private const FA_USER_NOT_FOUND_CONTENTS_SEARCH_STRING = 'This user cannot be found.';
    private const FA_SYSTEM_ERROR_CONTENTS_SEARCH_STRING = '<title>System Error</title>';

    private const TRELLO_BOARD_URL_REGEXP = '^https?://trello.com/b/(?<boardId>[a-zA-Z0-9]+)/';
    private const INSTAGRAM_URL_REGEXP = '^https?://(?:www\.)?instagram\.com/(?<username>[^/]+)/?$';

    private readonly Detector $detector;
    private readonly Pattern $trelloBoardUrlPattern;
    private readonly Pattern $instagramUrlPattern;

    public function __construct()
    {
        $this->detector = new Detector();

        $this->trelloBoardUrlPattern = pattern(self::TRELLO_BOARD_URL_REGEXP);
        $this->instagramUrlPattern = pattern(WebsiteInfo::INSTAGRAM_URL_REGEXP);
    }

    public static function getTrelloBoardDataUrl(string $boardId): string
    {
        return "https://trello.com/1/Boards/$boardId?lists=open&list_fields=name&cards=visible&card_attachments=false&card_stickers=false&card_fields=desc%2CdescData%2Cname&card_checklists=none&members=none&member_fields=none&membersInvited=none&membersInvited_fields=none&memberships_orgMemberType=false&checklists=none&organization=false&organization_fields=none%2CdisplayName%2Cdesc%2CdescData%2Cwebsite&organization_tags=false&myPrefs=false&fields=name%2Cdesc%2CdescData";
    }

    public function getInstagramUserProfileDataUrl(string $username): string
    {
        return "https://i.instagram.com/api/v1/users/web_profile_info/?username=$username";
    }

    /**
     * @return string[]
     */
    public function getChildrenUrls(Snapshot $webpageSnapshot): array
    {
        if ($this->detector->isWixsite($webpageSnapshot)) {
            return $this->getWixsiteDependencyUrls($webpageSnapshot);
        } elseif ($this->detector->isTrello($webpageSnapshot)) {
            return $this->getTrelloDependencyUrls($webpageSnapshot);
        } elseif ($this->detector->isInstagram($webpageSnapshot)) {
            return $this->getInstagramDependencyUrls($webpageSnapshot);
        } else {
            return [];
        }
    }

    /**
     * @return string[]
     */
    private function getWixsiteDependencyUrls(Snapshot $webpageSnapshot): array
    {
        // There used to be links '<link[^>]* href="(?<dataUrl>https://static.wixstatic.com/sites/[a-z0-9_]+\.json\.z\?v=\d+)"[^>]*>' containing data, not all's in HTML.
        return [];
    }

    /**
     * @return string[]
     */
    private function getTrelloDependencyUrls(Snapshot $webpageSnapshot): array
    {
        return $this->trelloBoardUrlPattern
            ->match($webpageSnapshot->url)
            ->findFirst()
            ->map(function (Detail $detail): array {
                try {
                    return [self::getTrelloBoardDataUrl($detail->get('boardId'))];
                } catch (NonexistentGroupException $e) { // @codeCoverageIgnoreStart
                    throw new UnbelievableRuntimeException($e);
                } // @codeCoverageIgnoreEnd
            })
            ->orReturn([]);
    }

    /**
     * @return string[]
     */
    private function getInstagramDependencyUrls(Snapshot $webpageSnapshot): array
    {
        return $this->instagramUrlPattern
            ->match($webpageSnapshot->url)
            ->findFirst()
            ->map(function (Detail $detail): array {
                try {
                    return [self::getInstagramUserProfileDataUrl($detail->get('username'))];
                } catch (NonexistentGroupException $e) { // @codeCoverageIgnoreStart
                    throw new UnbelievableRuntimeException($e);
                } // @codeCoverageIgnoreEnd
            })
            ->orReturn([]);
    }

    public function getLatentCode(string $url, string $content): ?int
    {
        if ($this->detector->isFurAffinity($url)) {
            if (contains_ci($content, self::FA_ACCOUNT_DISABLED_CONTENTS_SEARCH_STRING)) {
                return 410;
            } elseif (contains_ci($content, self::FA_ACCOUNT_LOGIN_REQUIRED_CONTENTS_SEARCH_STRING)) {
                return 401;
            } elseif (contains_ci($content, self::FA_USER_NOT_FOUND_CONTENTS_SEARCH_STRING)
                && contains_ci($content, self::FA_SYSTEM_ERROR_CONTENTS_SEARCH_STRING)) {
                return 404;
            }
        }

        return null;
    }
}
