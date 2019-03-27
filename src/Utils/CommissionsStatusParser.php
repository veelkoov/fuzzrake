<?php

declare(strict_types=1);

namespace App\Utils;

use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class CommissionsStatusParser
{
    /**
     * @var Regexp[]
     */
    private $falsePositivesRegexps;

    /**
     * @var Regexp[]
     */
    private $statusRegexps;

    /**
     * @var RegexpVariant
     */
    private $open;
    /**
     * @var RegexpVariant
     */
    private $closed;

    public function __construct()
    {
        $this->open = new RegexpVariant(['STATUS' => 'OPEN']);
        $this->closed = new RegexpVariant(['STATUS' => 'CLOSED']);
        $this->any = new RegexpVariant(['STATUS' => '(OPEN|CLOSED)']);

        $rf = new RegexpFactory(CommissionsStatusRegexps::COMMON_REPLACEMENTS);
        $this->falsePositivesRegexps = $rf->createSet(CommissionsStatusRegexps::FALSE_POSITIVES_REGEXES, [$this->any]);
        $this->statusRegexps = $rf->createSet(CommissionsStatusRegexps::GENERIC_REGEXES, [$this->open, $this->closed]);

//        $this->debugDumpRegexpes();
    }

    /**
     * @param string $inputText
     * @param string $additionalFilter
     *
     * @return bool
     *
     * @throws CommissionsStatusParserException
     */
    public function areCommissionsOpen(string $inputText, string $studioName, string $additionalFilter = ''): bool
    {
        $inputText = self::cleanHtml($inputText);
        $inputText = str_ireplace($studioName, 'STUDIO_NAME', $inputText);

        try {
            $inputText = self::applyFilters($inputText, $additionalFilter);
        } catch (InvalidArgumentException $ex) {
            throw new CommissionsStatusParserException("Filtering failed ({$ex->getMessage()})");
        }

        $open = $this->matchesGivenRegexpSet($inputText, $this->statusRegexps, $this->open);
        $closed = $this->matchesGivenRegexpSet($inputText, $this->statusRegexps, $this->closed);

        return self::analyseResult($open, $closed);
    }

    private function matchesGivenRegexpSet(string $testedString, array $regexpSet, RegexpVariant $variant): bool
    {
        foreach ($regexpSet as $regexp) {
            if ($regexp->matches($testedString, $variant)) {
                return true;
            }
        }

        return false;
    }

    private function cleanHtml(string $webpage): string
    {
        $webpage = strtolower($webpage);
        $webpage = self::extractFromJson($webpage);

        foreach (CommissionsStatusRegexps::HTML_CLEANER_REGEXPS as $regexp => $replacement) {
            $webpage = preg_replace($regexp, $replacement, $webpage);
        }

        foreach ($this->falsePositivesRegexps as $regexp) {
            $webpage = $regexp->removeFrom($webpage);
        }

        return $webpage;
    }

    /**
     * @param string $inputText
     * @param string $additionalFilter
     *
     * @return string
     *
     * @throws CommissionsStatusParserException
     */
    private static function applyFilters(string $inputText, string $additionalFilter): string
    {
        if (WebsiteInfo::isFurAffinity(null, $inputText)) {
            if (false !== stripos($inputText, '<p class="link-override">The owner of this page has elected to make it available to registered users only.')) {
                throw new CommissionsStatusParserException('FurAffinity login required');
            }

            if (WebsiteInfo::isFurAffinityUserProfile(null, $inputText)) {
                $additionalFilter = 'profile' === $additionalFilter ? 'td[width="80%"][align="left"]' : '';

                $crawler = new Crawler($inputText);

                return $crawler->filter("#page-userpage tr:first-child table.maintable $additionalFilter")->html();
            }

            return $inputText;
        }

        if (WebsiteInfo::isTwitter($inputText)) {
            $crawler = new Crawler($inputText);

            return $crawler->filter('div.profileheadercard')->html();
        }

        if (WebsiteInfo::isInstagram($inputText)) {
            $crawler = new Crawler($inputText);

            return $crawler->filter('script[type="application/ld+json"]')->html();
        }

        return $inputText;
    }

    /**
     * @param bool $open
     * @param bool $closed
     *
     * @return bool
     *
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

    private static function extractFromJson(string $webpage)
    {
        if (empty($webpage) || '{' !== $webpage[0]) {
            return $webpage;
        }

        $result = json_decode($webpage, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return $webpage;
        }

        return self::flattenArray($result);
    }

    /**
     * https://stackoverflow.com/questions/1319903/how-to-flatten-a-multidimensional-array#comment7768057_1320156.
     *
     * @param array $array
     *
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
            echo "{$regex->getCompiled()}\n";
        }
        echo "OPEN ====================================================\n";
        foreach ($this->statusRegexps as $regex) {
            echo "{$regex->getCompiled($this->open)}\n";
        }
        echo "CLOSED ==================================================\n";
        foreach ($this->statusRegexps as $regex) {
            echo "{$regex->getCompiled($this->closed)}\n";
        }
    }
}
