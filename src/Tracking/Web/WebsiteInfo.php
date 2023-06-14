<?php

declare(strict_types=1);

namespace App\Tracking\Web;

use function Psl\Str\contains_ci;

class WebsiteInfo
{
    private const FA_ACCOUNT_DISABLED_CONTENTS_SEARCH_STRING = '<title>Account disabled. -- Fur Affinity [dot] net</title>';
    private const FA_ACCOUNT_LOGIN_REQUIRED_CONTENTS_SEARCH_STRING = '<p class="link-override">The owner of this page has elected to make it available to registered users only.';
    private const FA_USER_NOT_FOUND_CONTENTS_SEARCH_STRING = 'This user cannot be found.';
    private const FA_SYSTEM_ERROR_CONTENTS_SEARCH_STRING = '<title>System Error</title>';

    private readonly Detector $detector;

    public function __construct()
    {
        $this->detector = new Detector();
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
