<?php

namespace App\Utils;

class UrlFetcher
{
    public function fetchWebPage($url): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; GetFursuitBot/0.1; +http://getfursu.it/)');

        $result = curl_exec($ch);

        if ($result === false) {
            throw new \LogicException("Failed to fetch URL: $url, " . curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }
}
