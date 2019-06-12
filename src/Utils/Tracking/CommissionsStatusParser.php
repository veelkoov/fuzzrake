<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Regexp\Factory;
use App\Utils\Regexp\Regexp;
use App\Utils\Regexp\RegexpFailure;
use App\Utils\Regexp\Variant;
use App\Utils\Web\WebpageSnapshot;
use InvalidArgumentException;

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
     * @var Variant
     */
    private $open;

    /**
     * @var Variant
     */
    private $closed;

    /**
     * @var Variant
     */
    private $any;

    public function __construct()
    {
        $this->open = new Variant(['STATUS' => 'OPEN']);
        $this->closed = new Variant(['STATUS' => 'CLOSED']);
        $this->any = new Variant(['STATUS' => '(OPEN|CLOSED)']);

        $rf = new Factory(CommissionsStatusRegexps::COMMON_REPLACEMENTS);
        $this->falsePositivesRegexps = $rf->createSet(CommissionsStatusRegexps::FALSE_POSITIVES_REGEXES, [$this->any]);
        $this->statusRegexps = $rf->createSet(CommissionsStatusRegexps::GENERIC_REGEXES, [$this->open, $this->closed]);

//        $this->debugDumpRegexpes();
    }

    /**
     * @param WebpageSnapshot $snapshot
     *
     * @return bool
     *
     * @throws TrackerException
     */
    public function areCommissionsOpen(WebpageSnapshot $snapshot): bool
    {
        $additionalFilter = HtmlPreprocessor::guessFilterFromUrl($snapshot->getUrl());
        $artisanName = $snapshot->getOwnerName();

        $inputTexts = array_map(function (string $input) use ($artisanName, $additionalFilter) {
            return $this->processInputText($artisanName, $additionalFilter, $input);
        }, $snapshot->getAllContents());

        $open = $this->matchesGivenRegexpSet($inputTexts, $this->statusRegexps, $this->open);
        $closed = $this->matchesGivenRegexpSet($inputTexts, $this->statusRegexps, $this->closed);

        return $this->analyseResult($open, $closed);
    }

    /**
     * TODO: Move into HtmlPreprocessor.
     *
     * @param string $artisanName
     * @param string $additionalFilter
     * @param string $inputText
     *
     * @return string
     *
     * @throws TrackerException
     * @throws RegexpFailure
     */
    private function processInputText(string $artisanName, string $additionalFilter, string $inputText): string
    {
        $inputText = HtmlPreprocessor::cleanHtml($inputText);
        $inputText = HtmlPreprocessor::processArtisansName($artisanName, $inputText);
        $inputText = $this->removeFalsePositives($inputText);

        try {
            $inputText = HtmlPreprocessor::applyFilters($inputText, $additionalFilter);
        } catch (InvalidArgumentException $ex) {
            throw new TrackerException("Filtering failed ({$ex->getMessage()})");
        }

        return $inputText;
    }

    /**
     * @param string $inputText
     *
     * @return string
     *
     * @throws RegexpFailure
     */
    private function removeFalsePositives(string $inputText): string
    {
        foreach ($this->falsePositivesRegexps as $regexp) {
            $inputText = $regexp->removeFrom($inputText);
        }

        return $inputText;
    }

    private function matchesGivenRegexpSet(array $testedStrings, array $regexpSet, Variant $variant): bool
    {
        foreach ($testedStrings as $testedString) {
            foreach ($regexpSet as $regexp) {
                if ($regexp->matches($testedString, $variant)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param bool $open
     * @param bool $closed
     *
     * @return bool
     *
     * @throws TrackerException
     */
    private function analyseResult(bool $open, bool $closed): bool
    {
        if ($open && !$closed) {
            return true;
        }

        if ($closed && !$open) {
            return false;
        }

        if ($open) { // && $closed
            throw new TrackerException('BOTH matches');
        } else {
            throw new TrackerException('NONE matches');
        }
    }

    /**
     * @throws RegexpFailure
     */
    private function debugDumpRegexpes(): void
    {
        echo "FALSE-POSITIVES =========================================\n";
        foreach ($this->falsePositivesRegexps as $regexp) {
            echo "{$regexp->getCompiled()}\n";
        }
        echo "OPEN ====================================================\n";
        foreach ($this->statusRegexps as $regexp) {
            echo "{$regexp->getCompiled($this->open)}\n";
        }
        echo "CLOSED ==================================================\n";
        foreach ($this->statusRegexps as $regexp) {
            echo "{$regexp->getCompiled($this->closed)}\n";
        }
    }
}
