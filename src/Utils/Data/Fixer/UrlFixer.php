<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Artisan\Fields;
use App\Utils\Regexp\Regexp;

class UrlFixer extends StringFixer
{
    public function fix(string $fieldName, string $subject): string
    {
        $subject = parent::fix($fieldName, $subject);

        switch ($fieldName) {
            case Fields::URL_FUR_AFFINITY:
                return $this->fixFurAffinityUrl($subject);

            case Fields::URL_TWITTER:
                return $this->fixTwitterUrl($subject);

            case Fields::URL_INSTAGRAM:
                return $this->fixInstagramUrl($subject);

            case Fields::URL_FACEBOOK:
                return $this->fixFacebookUrl($subject);

            case Fields::URL_YOUTUBE:
                return $this->fixYoutubeUrl($subject);

            case Fields::URL_DEVIANTART:
                return $this->fixDeviantArtUrl($subject);

            default:
                return $subject;
        }
    }

    private function fixFurAffinityUrl(string $subject): string
    {
        return Regexp::replace('#^(?:https?://)?(?:www\.)?furaffinity(?:\.net|\.com)?/(?:user/|gallery/)?([^/]+)/?$#i',
            'http://www.furaffinity.net/user/$1', $subject);
    }

    private function fixTwitterUrl(string $subject): string
    {
        return Regexp::replace('#^(?:(?:(?:https?://)?(?:www\.|mobile\.)?twitter(?:\.com)?/)|@)([^/?]+)/?(?:\?(?:lang=[a-z]{2,3}|s=\d+))?$#i',
            'https://twitter.com/$1', $subject);
    }

    private function fixInstagramUrl(string $subject): string
    {
        return Regexp::replace('#^(?:(?:(?:https?://)?(?:www\.)?instagram(?:\.com)?/)|@)([^/?]+)/?(?:\?hl=[a-z]{2,3}(?:-[a-z]{2,3})?)?$#i',
            'https://www.instagram.com/$1/', $subject);
    }

    private function fixFacebookUrl(string $subject): string
    {
        return Regexp::replace('#^(?:https?://)?(?:www\.|m\.|business\.)?facebook\.com/(?:pg/)?([^/?]+)(?:/posts|/about)?/?(\?(?!id=)[a-z_]+=[a-z_0-9]+)?$#i',
            'https://www.facebook.com/$1/', $subject);
    }

    private function fixYoutubeUrl(string $subject): string
    {
        return Regexp::replace('#^(?:https?://)?(?:www|m)\.youtube\.com/((?:channel|user|c)/[^/?]+)(?:/featured)?(/|\?view_as=subscriber)?$#',
            'https://www.youtube.com/$1', $subject);
    }

    private function fixDeviantArtUrl(string $subject): string
    {
        $subject = Regexp::replace('#^(?:https?://)?(?:www\.)?deviantart(?:\.net|\.com)?/([^/]+)(?:/gallery)?/?$#i',
            'https://www.deviantart.com/$1', $subject);
        $subject = Regexp::replace('#^(?:https?://)?(?:www\.)?([^.]+)\.deviantart(?:\.net|\.com)?/?$#i',
            'https://$1.deviantart.com/', $subject);

        return $subject;
    }
}
