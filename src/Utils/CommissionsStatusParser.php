<?php
declare(strict_types=1);

namespace App\Utils;


use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class CommissionsStatusParser
{
    const HTML_CLEANER_REGEXPS = [
        '#</?(strong|b|i|span|center|a|em)[^>]*>#s' => '',
        '#(\s|&nbsp;|<br\s*/?>)+#s' => ' ',
        '#<style[^>]*>.*?</style>#s' => '',
        '# style="[^"]*"( (?=\>))?#s' => '',
        '#’#' => '\'',
    ];
    const FALSE_POSITIVES = [
        '#once commissions are open#s',
    ];
    const GENERIC_REGEXES = [
        'WE_ARE currently (STATUS|\*\*\*STATUS\*\*\*) for ((the )?commissions|new projects|new orders)',
        'commissions(( and)? quotes)?( status| are)?( ?:| now| currently ?:?| at this time are)? ?STATUS',
        'quotes have now STATUS',
        '(?!will not be )STATUS for (new )?(quotes and )?commissions ?([.!]|</)',
        'STATUS for (new )?(quotes and )?commissions ?([.!]|</)',
        'quote reviews are STATUS!',
        '(fursuits )?commissions(:? are| info)? STATUS',
        '(^|\.) ?STATUS for commissions ?($|[.(])',
        '<div>currently</div><div>STATUS</div><div>for commissions</div>',
        '<p>commissions are</p><p>STATUS</p>',
        '\[ commissions[. ]+STATUS \]',
    ];
    const EXTRA_OPEN_REGEXES = [
        'right now WE_CAN take some fursuit commissions',
    ];
    const EXTRA_CLOSED_REGEXES = [
        'WE_ARE currently closed for everything except heads, partials are on an occasional basis\. not accepting fullsuits until all on my queue are done\.',
        'WE_ARE not seeking any more commissions until further notice',
    ];
    const COMMON_REPLACEMENTS = [
        'commissions' => 'comm?iss?ions?',
        'open' => 'open(?!ing)',
        'closed' => 'closed?',
        'fursuits' => 'fursuits?',
        '</div>' => ' ?</div> ?',
        '<div>' => ' ?<div( class="[^"]*")?> ?',
        '<p>' => ' ?<p( class="[^"]*")?> ?',
        '</p>' => ' ?</p> ?',
        'WE_CAN' => '(i|we) can',
        'WE_ARE' => '(we are|we\'re|i am)',
    ];

    private $statusOpenRegexps;
    private $statusClosedRegexps;

    public function __construct()
    {
        $this->statusOpenRegexps = self::getStatusRegexes('open', self::EXTRA_OPEN_REGEXES);
        $this->statusClosedRegexps = self::getStatusRegexes('closed', self::EXTRA_CLOSED_REGEXES);

//        $this->debugDumpRegexpes();
    }

    /**
     * @param string $inputText
     * @return bool
     * @throws CommissionsStatusParserException
     */
    public function areCommissionsOpen(string $inputText): bool
    {
        $inputText = self::cleanHtml($inputText);

        try {
            $inputText = self::applyFilters($inputText);
        } catch (InvalidArgumentException $ex) {
            throw new CommissionsStatusParserException("Filtering failed ({$ex->getMessage()})");
        }

        $open = $this->matchesGivenRegexpSet($inputText, $this->statusOpenRegexps);
        $closed = $this->matchesGivenRegexpSet($inputText, $this->statusClosedRegexps);

        return self::analyseResult($open, $closed);
    }

    private function matchesGivenRegexpSet(string $testedString, array $regexpSet): bool
    {
        foreach ($regexpSet as $regex) {
            if (self::matchesGivenRegexp($regex, $testedString)) {
                return true;
            }
        }

        return false;
    }

    private static function matchesGivenRegexp(string $regex, string $testedString): bool
    {
        $result = preg_match($regex, $testedString);

        if ($result === null) {
            throw new \LogicException("Regex matching failed: $regex", preg_last_error());
        }

        return $result === 1;
    }

    private function cleanHtml(string $webpage): string
    {
        $webpage = strtolower($webpage);
        $webpage = self::extractFromJson($webpage);

        foreach (self::HTML_CLEANER_REGEXPS as $regexp => $replacement) {
            $webpage = preg_replace($regexp, $replacement, $webpage);
        }

        foreach (self::FALSE_POSITIVES as $regexp) {
            $webpage = preg_replace($regexp, '', $webpage);
        }

        return $webpage;
    }

    /**
     * @param string $inputText
     * @return string
     * @throws CommissionsStatusParserException
     */
    private static function applyFilters(string $inputText): string
    {
        if (WebsiteInfo::isFurAffinity(null, $inputText)) {
            if (stripos($inputText, '<p class="link-override">The owner of this page has elected to make it available to registered users only.') !== false) {
                throw new CommissionsStatusParserException("FurAffinity login required");
            }

            $crawler = new Crawler($inputText);
            return $crawler->filter('#page-userpage tr:first-child table.maintable')->html();
        }

        if (WebsiteInfo::isTwitter($inputText)) {
            $crawler = new Crawler($inputText);
            return $crawler->filter('div.profileheadercard p.profileheadercard-bio.u-dir')->html();
        }

        return $inputText;
    }

    /**
     * @param bool $open
     * @param bool $closed
     * @return bool
     * @throws CommissionsStatusParserException
     */
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

    private static function getStatusRegexes(string $status, array $extraRegexes): array
    {
        return array_map(function ($regex) use ($status) {
            $regex = str_replace('STATUS', $status, $regex);

            foreach (self::COMMON_REPLACEMENTS as $needle => $replacement) {
                $regex = str_replace($needle, $replacement, $regex);
            }

            return "#$regex#s";
        }, array_merge(self::GENERIC_REGEXES, $extraRegexes));
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
     * @param array $array
     * @return string
     */
    private static function flattenArray(array $array)
    {
        $result = '';

        array_walk_recursive($array, function ($a, $b) use (&$result) {
            $result .= "$b: $a\n";
        });

        return $result;
    }

    private function debugDumpRegexpes(): void
    {
        echo "OPEN ====================================================\n";
        foreach ($this->statusOpenRegexps as $regex) {
            echo "$regex\n";
        }
        echo "CLOSED ==================================================\n";
        foreach ($this->statusClosedRegexps as $regex) {
            echo "$regex\n";
        }
    }
}
