<?php

namespace App\Utils;


class CommissionsOpenParser
{
    const HTML_CLEANER_REGEXPS = [
        '</?(strong|b|i|span|center|a|em)[^>]*>' => '',
        '(\s|&nbsp;|<br\s*/?>)+' => ' ',
    ];
    const OPEN_REGEXES = [
        'commissions open ?\.',
        '!+ ?commissions open ?!+',
        'we are currently open for (the )?commissions',
        'commissions? (status|are) ?: ?open',
        'commissions and quotes ?: ?open',
        'quotes have now opened', // TODO: verify if makes sense
        'open for (new )?(quotes and )?commissions ?[.!]',
        'quote reviews are open!',
    ];
    const CLOSED_REGEXES = [
        'commissions closed? ?\.',
        '!+ ?commissions closed? ?!+',
        'we are currently closed? for (the )?commissions',
        'commissions? (status|are) ?: ?closed?',
        'commissions and quotes ?: ?closed?',
        'quotes have now closed', // TODO: verify if makes sense
        'closed for (new )?(quotes and )?commissions ?[.!]',
        'quote reviews are closed!',
    ];

    public static function areCommissionsOpen(string $inputText): ?bool
    {
        $inputText = self::cleanHtml($inputText);

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
}
