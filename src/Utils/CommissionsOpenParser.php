<?php

namespace App\Utils;


use Symfony\Component\DomCrawler\Crawler;

class CommissionsOpenParser
{
    const HTML_CLEANER_REGEXPS = [
        '</?(strong|b|i|span|center|a|em)[^>]*>' => '',
        '(\s|&nbsp;|<br\s*/?>)+' => ' ',
    ];
    const OPEN_REGEXES = [
        'we are currently open for (the )?commissions',
        'commissions?(( and)? quotes)?( status| are)?( ?:| now| currently)? ?open',
        'quotes have now opened', // TODO: verify if makes sense
        'open for (new )?(quotes and )?commissions ?[.!]',
        'quote reviews are open!',
        'commissions? info open ?!',
    ];
    const CLOSED_REGEXES = [
        'we are currently closed? for (the )?commissions',
        'commissions?(( and)? quotes)?( status| are)?( ?:| now| currently)? ?closed?',
        'quotes have now closed', // TODO: verify if makes sense
        'closed for (new )?(quotes and )?commissions ?[.!]',
        'quote reviews are closed!',
        'commissions? info closed? ?!',
    ];

    public static function areCommissionsOpen(string $inputText): ?bool
    {
        $inputText = self::cleanHtml($inputText);
        $inputText = self::applyFilters($inputText);

        $open = self::matchesOpen($inputText);
        $closed = self::matchesClosed($inputText);

        if ($open && !$closed) {
            return true;
        }

        if ($closed && !$open) {
            return false;
        }

        return null;
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
        $result = preg_match("#$regex#", $testedString);
        if ($result === null) {
            throw new \LogicException("Regex matching failed: $regex", preg_last_error());
        }

        return $result;
    }

    private static function cleanHtml(string $webpage): string
    {
        foreach (self::HTML_CLEANER_REGEXPS as $regexp => $replacement) {
            $webpage = preg_replace("#$regexp#si", $replacement, $webpage);
        }

        return strtolower($webpage);
    }

    private static function applyFilters(string $inputText): string
    {
        if (strpos($inputText, 'fur affinity [dot] net</title>') !== false) {
            $crawler = new Crawler($inputText);
            return $crawler->filter('#page-userpage tr:first-child table.maintable')->html();
        }

        return $inputText;
    }
}
