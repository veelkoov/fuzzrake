<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Regexp;
use App\Utils\StrUtils;
use App\Utils\UnbelievableRuntimeException;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\PatternInterface;

class LanguagesFixer extends StringFixer
{
    private PatternInterface $pattern;

    /**
     * @var string[]
     */
    private array $replacements;

    public function __construct(array $languages, array $strings)
    {
        parent::__construct($strings);

        $this->pattern = pattern($languages['regexp'], 'i');
        $this->replacements = $languages['replacements'];
    }

    public function fix(string $fieldName, string $subject): string
    {
        $subject = parent::fix($fieldName, $subject);

        $subject = pattern('[\n,;&]|[, ]and ')->split($subject);
        $subject = array_filter(array_map('trim', $subject));
        $subject = array_map(function (string $language): string {
            $language = Regexp::replaceAll($this->replacements, $language);

            return $this->pattern->replace($language)->first()->callback(function (Detail $detail): string {
                try {
                    $language = $detail->get('language');
                    $limited = $detail->matched('prefix') || $detail->matched('suffix');
                } catch (NonexistentGroupException $e) {
                    throw new UnbelievableRuntimeException($e);
                }

                $language = StrUtils::ucfirst($language);

                return $language.($limited ? ' (limited)' : '');
            });
        }, $subject);

        sort($subject);

        return implode("\n", $subject);
    }
}
