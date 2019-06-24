<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Regexp\Factory;
use App\Utils\Regexp\Match;
use App\Utils\Regexp\Regexp;
use App\Utils\Regexp\RegexpFailure;
use App\Utils\Regexp\Variant;
use App\Utils\Web\WebpageSnapshot;

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

//        $this->debugDumpRegexps();
    }

    /**
     * @param WebpageSnapshot $snapshot
     *
     * @return AnalysisResult
     *
     * @throws TrackerException From inside array_map
     * @throws RegexpFailure
     */
    public function analyseStatus(WebpageSnapshot $snapshot): AnalysisResult
    {
        $additionalFilter = HtmlPreprocessor::guessFilterFromUrl($snapshot->getUrl());
        $artisanName = $snapshot->getOwnerName();

        $inputTexts = array_map(function (string $input) use ($artisanName, $additionalFilter) {
            return $this->processInputText($artisanName, $additionalFilter, $input);
        }, $snapshot->getAllContents());

        $open = $this->findMatch($inputTexts, $this->statusRegexps, $this->open);
        $closed = $this->findMatch($inputTexts, $this->statusRegexps, $this->closed);

        return new AnalysisResult($open, $closed);
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
        $inputText = HtmlPreprocessor::applyFilters($inputText, $additionalFilter);

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

    /**
     * @param string[] $testedStrings
     * @param Regexp[] $regexpSet
     * @param Variant  $variant
     *
     * @return Match|null
     *
     * @throws RegexpFailure
     */
    private function findMatch(array $testedStrings, array $regexpSet, Variant $variant): ?Match
    {
        foreach ($testedStrings as $testedString) {
            foreach ($regexpSet as $regexp) {
                if ($result = $regexp->matches($testedString, $variant)) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * @throws RegexpFailure
     */
    private function debugDumpRegexps(): void
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
