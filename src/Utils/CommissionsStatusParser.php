<?php
declare(strict_types=1);

namespace App\Utils;


use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class CommissionsStatusParser
{
    const HTML_CLEANER_REGEXPS = [
        '#</?(strong|b|i|span|center|a|em|font)[^>]*>#s' => '',
        '#(\s|&nbsp;|<br\s*/?>)+#s' => ' ',
        '#<style[^>]*>.*?</style>#s' => '',
        '# style="[^"]*"( (?=\>))?#s' => '',
        '#’#' => '\'',
    ];
    const FALSE_POSITIVES_REGEXES = [
        'once commissions are STATUS',
        'art commissions: STATUS',
        'commissions STATUS MONTHS',
    ];
    const GENERIC_REGEXES = [
        '((WE_ARE )?currently|currently WE_ARE) (STATUS|\*\*\*STATUS\*\*\*)( for)?( the| new)? (commissions|projects|orders|quotes)',
        'commissions((/| and | )quotes)?( status| are)?( ?:| now| currently ?:?| at this time are| permanently)? ?STATUS',
        'quotes have now STATUS',
        '(?!will not be )STATUS for (new )?(quotes and )?commissions ?([.!]|</)',
        'STATUS for (new )?(quotes and )?commissions ?([.!]|</)',
        'quote reviews are STATUS!',
        '(fursuits )?commissions(:? are| info)? STATUS',
        '(^|\.) ?STATUS for commissions ?($|[.(])',
        '<div>currently</div><div>STATUS</div><div>for commissions</div>',
        '<p>commissions are</p><p>STATUS</p>',
        '\[ commissions[. ]+STATUS \]',
        '<div class="([^"]*[^a-z])?commissions-STATUS"></div>',
        '<h2[^>]*>STATUS</h2>',
        'slots currently STATUS',
    ];
    const EXTRA_OPEN_REGEXES = [
        'right now WE_CAN take some fursuit commissions',
        '(?!(aren\'t)|(not?)) accepting commissions',
    ];
    const EXTRA_CLOSED_REGEXES = [
        'WE_ARE currently closed for everything except heads, partials are on an occasional basis\. not accepting fullsuits until all on my queue are done\.',
        'WE_ARE not seeking any more commissions until further notice',
        'currently not accepting new projects',
    ];
    const COMMON_REPLACEMENTS = [
        'commissions' => 'comm?iss?ions?',
        'open' => '(open(?!ing)|(?!not? )accepting)',
        'closed' => '(closed?|not? accepting)',
        'fursuits' => 'fursuits?',
        '</div>' => ' ?</div> ?',
        '<div>' => ' ?<div( class="[^"]*")?> ?',
        '<p>' => ' ?<p( class="[^"]*")?> ?',
        '</p>' => ' ?</p> ?',
        'WE_CAN' => '(i|we) can',
        'WE_ARE' => '(we are|we\'re|i am|i\'m)',
        'MONTHS' => '(january|jan|february|feb|march|mar|april|apr|may|may|june|jun|july|jul|august|aug|september|sep|sept|october|oct|november|nov|december|dec)',
    ];

    private $falsePositivesRegexps;
    private $statusOpenRegexps;
    private $statusClosedRegexps;

    public function __construct()
    {
        $this->falsePositivesRegexps = array_merge(self::getCompiledRegexes(self::FALSE_POSITIVES_REGEXES, 'open'),
            self::getCompiledRegexes(self::FALSE_POSITIVES_REGEXES, 'closed'));
        $this->statusOpenRegexps = self::getCompiledRegexes(self::GENERIC_REGEXES, 'open', self::EXTRA_OPEN_REGEXES);
        $this->statusClosedRegexps = self::getCompiledRegexes(self::GENERIC_REGEXES, 'closed', self::EXTRA_CLOSED_REGEXES);

//        $this->debugDumpRegexpes();
    }

    /**
     * @param string $inputText
     * @param string $additionalFilter
     * @return bool
     * @throws CommissionsStatusParserException
     */
    public function areCommissionsOpen(string $inputText, string $additionalFilter = ''): bool
    {
        $inputText = self::cleanHtml($inputText);

        try {
            $inputText = self::applyFilters($inputText, $additionalFilter);
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

        foreach ($this->falsePositivesRegexps as $regexp) {
            $webpage = preg_replace($regexp, '', $webpage);
        }

        return $webpage;
    }

    /**
     * @param string $inputText
     * @param string $additionalFilter
     * @return string
     * @throws CommissionsStatusParserException
     */
    private static function applyFilters(string $inputText, string $additionalFilter): string
    {
        if (WebsiteInfo::isFurAffinity(null, $inputText)) {
            if (stripos($inputText, '<p class="link-override">The owner of this page has elected to make it available to registered users only.') !== false) {
                throw new CommissionsStatusParserException("FurAffinity login required");
            }

            if (WebsiteInfo::isFurAffinityUserProfile(null, $inputText)) {
                $additionalFilter = $additionalFilter === 'profile' ? 'td[width="80%"][align="left"]' : '';

                $crawler = new Crawler($inputText);
                return $crawler->filter("#page-userpage tr:first-child table.maintable $additionalFilter")->html();
            }

            return $inputText;
        }

        if (WebsiteInfo::isTwitter($inputText)) {
            $crawler = new Crawler($inputText);
            return $crawler->filter('div.profileheadercard')->html();
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

    private static function getCompiledRegexes(array $rawRegexes, string $status, array $extraRegexes = []): array
    {
        return array_map(function ($regex) use ($status) {
            $regex = str_replace('STATUS', $status, $regex);

            foreach (self::COMMON_REPLACEMENTS as $needle => $replacement) {
                $regex = str_replace($needle, $replacement, $regex);
            }

            return "#$regex#s";
        }, array_merge($rawRegexes, $extraRegexes));
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
        echo "FALSE-POSITIVES =========================================\n";
        foreach ($this->falsePositivesRegexps as $regex) {
            echo "$regex\n";
        }
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
