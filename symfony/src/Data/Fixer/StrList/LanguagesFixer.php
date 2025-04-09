<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\String\ConfigurableStringFixer;
use App\Data\Fixer\String\GenericStringFixer;
use App\Utils\UnbelievableRuntimeException;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Detail;
use TRegx\CleanRegex\Pattern;

final class LanguagesFixer extends AbstractListFixer
{
    private readonly Pattern $replacementPattern;
    private readonly ConfigurableStringFixer $fixer;

    /**
     * @param psLanguagesFixerConfig $languages
     */
    public function __construct(
        #[Autowire(param: 'languages')] array $languages,
        private readonly GenericStringFixer $genericStringFixer,
    ) {
        $this->replacementPattern = pattern($languages['regexp'], 'i');

        $this->fixer = new ConfigurableStringFixer($languages);
    }

    #[Override]
    protected function getSeparatorRegexp(): string
    {
        return '[,;&]|[, ]and ';
    }

    #[Override]
    protected function fixItem(string $subject): string
    {
        $subject = $this->genericStringFixer->fix($subject);
        $subject = $this->fixer->fix($subject);

        return $this->replacementPattern->replace($subject)->first()->callback(function (Detail $detail): string {
            try {
                $language = $detail->get('language');
                $limited = $detail->matched('prefix') || $detail->matched('suffix');
            } catch (NonexistentGroupException $e) { // @codeCoverageIgnoreStart
                throw new UnbelievableRuntimeException($e);
            } // @codeCoverageIgnoreEnd

            $language = mb_ucfirst($language);

            return $language.($limited ? ' (limited)' : '');
        });
    }
}
