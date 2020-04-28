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
        return Regexp::replaceAll($this->replacements, trim($subject), $this->commonRegexPrefix, $this->commonRegexSuffix);
    }
}
