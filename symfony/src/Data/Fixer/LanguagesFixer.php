<?php

declare(strict_types=1);

namespace App\Data\Fixer;

use App\Utils\Regexp\Replacements;
use App\Utils\StrUtils;
use App\Utils\UnbelievableRuntimeException;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Detail;
use TRegx\CleanRegex\Pattern;

class LanguagesFixer extends StringFixer
{
    private readonly Pattern $replacementPattern;
    private readonly Replacements $replacements;
    private readonly Pattern $splitPattern;

    /**
     * @param psLanguagesFixerConfig $languages
     * @param psFixerConfig          $strings
     */
    public function __construct(array $languages, array $strings)
    {
        parent::__construct($strings);

        $this->splitPattern = pattern('[\n,;&]|[, ]and ');
        $this->replacementPattern = pattern($languages['regexp'], 'i');
        $this->replacements = new Replacements($languages['replacements'], 'i', $languages['regex_prefix'], $languages['regex_suffix']);
    }

    public function fix(string $subject): string
    {
        $subject = parent::fix($subject);

        $subject = $this->splitPattern->split($subject);
        $subject = array_filter(array_map('trim', $subject));
        $subject = array_map(function (string $language): string {
            $language = $this->replacements->do($language);

            return $this->replacementPattern->replace($language)->first()->callback(function (Detail $detail): string {
                try {
                    $language = $detail->get('language');
                    $limited = $detail->matched('prefix') || $detail->matched('suffix');
                } catch (NonexistentGroupException $e) { // @codeCoverageIgnoreStart
                    throw new UnbelievableRuntimeException($e);
                } // @codeCoverageIgnoreEnd

                $language = StrUtils::ucfirst($language);

                return $language.($limited ? ' (limited)' : '');
            });
        }, $subject);

        sort($subject);

        return implode("\n", $subject);
    }
}
