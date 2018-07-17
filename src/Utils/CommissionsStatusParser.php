<?php

namespace App\Utils;


use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class CommissionsStatusParser
{
    const HTML_CLEANER_REGEXPS = [
        '</?(strong|b|i|span|center|a|em)[^>]*>' => '',
        '(\s|&nbsp;|<br\s*/?>)+' => ' ',
    ];
    const OPEN_REGEXES = [
        'we are currently open for (the )?commissions',
        'commissions(( and)? quotes)?( status| are)?( ?:| now| currently)? ?open',
        'quotes have now opened', // TODO: verify if makes sense
        '(?!will not be )open for (new )?(quotes and )?commissions ?[.!]',
        'quote reviews are open!',
        'commissions(: are| info) open',
        '(^|\.) ?open for commissions ?($|[.(])',
    ];
    const CLOSED_REGEXES = [
        'we are currently closed? for (the )?commissions',
        'commissions(( and)? quotes)?( status| are)?( ?:| now| currently)? ?closed?',
        'quotes have now closed', // TODO: verify if makes sense
        'closed for (new )?(quotes and )?commissions ?[.!]',
        'quote reviews are closed!',
        'commissions(: are| info) closed?',
        '(^|\.) ?closed? for commissions ?($|[.(])',
    ];

    public static function areCommissionsOpen(string $inputText): bool
    {
        $inputText = self::cleanHtml($inputText);

        try {
            $inputText = self::applyFilters($inputText);
        } catch (InvalidArgumentException $ex) {
            throw new CommissionsStatusParserException("Filtering failed ({$ex->getMessage()})");
        }

        $open = self::matchesOpen($inputText);
        $closed = self::matchesClosed($inputText);

        return self::analyseResult($open, $closed);
    }

    private static function matchesOpen(string $inputText): bool
    {
        foreach (self::OPEN_REGEXES as $regex) {
            if (self::matches($regex, $inputText)) {
                return true;
            }
        }

        return false;
    }

    private static function matchesClosed(string $inputText): bool
    {
        foreach (self::CLOSED_REGEXES as $regexPrefab) {
            if (self::matches($regexPrefab, $inputText)) {
                return true;
            }
        }

        return false;
    }

    private static function matches(string $regex, string $testedString): bool
    {
        $regex = str_replace('commissions', 'comm?iss?ions?', $regex);

        $result = preg_match("#$regex#", $testedString);
        if ($result === null) {
            throw new \LogicException("Regex matching failed: $regex", preg_last_error());
        }

        return $result;
    }

    private static function cleanHtml(string $webpage): string
    {
        $webpage = strtolower($webpage);

        foreach (self::HTML_CLEANER_REGEXPS as $regexp => $replacement) {
            $webpage = preg_replace("#$regexp#s", $replacement, $webpage);
        }

        return $webpage;
    }

    private static function applyFilters(string $inputText): string
    {
        if (stripos($inputText, 'fur affinity [dot] net</title>') !== false) {
            if (stripos($inputText, '<p class="link-override">The owner of this page has elected to make it available to registered users only.') !== false) {
                throw new CommissionsStatusParserException("FurAffinity login required");
            }

            $crawler = new Crawler($inputText);
            return $crawler->filter('#page-userpage tr:first-child table.maintable')->html();
        }

        if (stripos($inputText, '| Twitter</title>') !== false) {
            $crawler = new Crawler($inputText);
            return $crawler->filter('div.profileheadercard p.profileheadercard-bio.u-dir')->html();
        }

        return $inputText;
    }

    private static function analyseResult(bool $open, bool $closed): bool
    {
        if ($open && !$closed) {
            return true;
        }

        if ($closed && !$open) {
            return false;
        }

        if ($open) { // && $closed
            throw new CommissionsStatusParserException('BOTH matches');
        } else {
            throw new CommissionsStatusParserException('NONE matches');
        }
    }
}
