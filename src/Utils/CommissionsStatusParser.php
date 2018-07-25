<?php

namespace App\Utils;


use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class CommissionsStatusParser
{
    const HTML_CLEANER_REGEXPS = [
        '</?(strong|b|i|span|center|a|em)[^>]*>' => '',
        '(\s|&nbsp;|<br\s*/?>)+' => ' ',
        '<style[^>]*>.*?</style>' => '',
        ' style="[^"]*"( (?=\>))?' => '',
    ];
    const OPEN_REGEXES = [
        '(we are|we\'re|i am) currently open for ((the )?commissions|new projects|new orders)',
        'commissions(( and)? quotes)?( status| are)?( ?:| now| currently ?:?| at this time are)? ?open',
        'quotes have now opened', // TODO: verify if makes sense
        '(?!will not be )open for (new )?(quotes and )?commissions ?([.!]|</)',
        'quote reviews are open!',
        '(fursuits )?commissions(:? are| info)? open',
        '(^|\.) ?open for commissions ?($|[.(])',
        '<div>currently</div><div>open</div><div>for commissions</div>',
        '<p>commissions are</p><p>open</p>',
    ];
    const CLOSED_REGEXES = [
        '(we are|we\'re|i am) currently closed for ((the )?commissions|new projects|new orders)',
        'commissions(( and)? quotes)?( status| are)?( ?:| now| currently ?:?| at this time are)? ?closed',
        'quotes have now closed', // TODO: verify if makes sense
        'closed for (new )?(quotes and )?commissions ?([.!]|</)',
        'quote reviews are closed!',
        '(fursuits )?commissions(:? are| info)? closed',
        '(^|\.) ?closed for commissions ?($|[.(])',
        '<div>currently</div><div>closed</div><div>for commissions</div>',
        '<p>commissions are</p><p>closed</p>',
    ];
    const COMMON_REPLACEMENTS = [
        'commissions' => 'comm?iss?ions?',
        'open' => 'open(?!ing)',
        'closed' => 'closed?',
        'fursuits' => 'fursuits?',
        '</div>' => ' ?</div> ?',
        '<div>' => ' ?<div> ?',
        '<p>' => ' ?<p> ?',
        '</p>' => ' ?</p> ?',
        '<p>' => '<p( class="[^"]*")?>',
        '<div>' => '<div( class="[^"]*")?>',
    ];
    const FALSE_POSITIVES = [
        'once commissions are open',
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
        foreach (self::getRegexes(self::OPEN_REGEXES) as $regex) {
            if (self::matches($regex, $inputText)) {
                return true;
            }
        }

        return false;
    }

    private static function matchesClosed(string $inputText): bool
    {
        foreach (self::getRegexes(self::CLOSED_REGEXES) as $regexPrefab) {
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
        $webpage = strtolower($webpage);
        $webpage = self::extractFromJson($webpage);

        foreach (self::HTML_CLEANER_REGEXPS as $regexp => $replacement) {
            $webpage = preg_replace("#$regexp#s", $replacement, $webpage);
        }

        foreach (self::FALSE_POSITIVES as $regexp) {
            $webpage = preg_replace("#$regexp#s", '', $webpage);
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

    private static function getRegexes(array $regexes): array
    {
        return array_map(function ($regex) {
            foreach (self::COMMON_REPLACEMENTS as $needle => $replacement) {
                $regex = str_replace($needle, $replacement, $regex);
            }

            return $regex;
        }, $regexes);
    }

    private static function extractFromJson(string $webpage)
    {
        if (empty($webpage) || $webpage[0] !== '{') {
            return $webpage;
        }

        $result = json_decode($webpage, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $webpage;
        }

        return self::flattenArray($result);
    }

    /**
     * https://stackoverflow.com/questions/1319903/how-to-flatten-a-multidimensional-array#comment7768057_1320156
     */
    private static function flattenArray(array $array)
    {
        $result = '';

        array_walk_recursive($array, function ($a, $b) use (&$result) {
            $result .= "$b: $a\n";
        });

        return $result;
    }
}
