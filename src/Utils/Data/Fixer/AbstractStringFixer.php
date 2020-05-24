<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Regexp;

class AbstractStringFixer implements FixerInterface
{
    /**
     * @var string[]
     */
    private array $replacements;
    private string $commonRegexPrefix;
    private string $commonRegexSuffix;

    public function __construct(array $regexes)
    {
        $this->replacements = $regexes['replacements'];
        $this->commonRegexPrefix = $regexes['commonRegexPrefix'];
        $this->commonRegexSuffix = $regexes['commonRegexSuffix'];
    }

    public function fix(string $fieldName, string $subject): string
    {
        return $this->fixWith($this->replacements, trim($subject), $this->commonRegexPrefix, $this->commonRegexSuffix);
    }

    protected function fixWith(array $replacements, string $subject, string $commonRegexPrefix, string $commonRegexSuffix): string
    {
        return Regexp::replaceAll($replacements, $subject, $commonRegexPrefix, $commonRegexSuffix);
    }
}
