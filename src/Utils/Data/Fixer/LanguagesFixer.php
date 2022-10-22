<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Replacements;
use App\Utils\StrUtils;
use App\Utils\UnbelievableRuntimeException;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

class LanguagesFixer extends StringFixer
{
    private readonly Pattern $pattern;
    private readonly Replacements $replacements;

    /**
     * @param psLanguagesFixerConfig $languages
     * @param psFixerConfig          $strings
     */
    public function __construct(array $languages, array $strings)
    {
        parent::__construct($strings);

        $this->pattern = pattern($languages['regexp'], 'i');
        $this->replacements = new Replacements($languages['replacements'], 'i', $languages['regex_prefix'], $languages['regex_suffix']);
    }

    public function fix(string $subject): string
    {
        $subject = parent::fix($subject);

        $subject = pattern('[\n,;&]|[, ]and ')->split($subject);
        $subject = array_filter(array_map('trim', $subject));
        $subject = array_map(function (string $language): string {
            $language = $this->replacements->do($language);

            return $this->pattern->replace($language)->first()->callback(function (Detail $detail): string {
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
