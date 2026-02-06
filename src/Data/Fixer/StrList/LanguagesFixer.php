<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\String\ConfigurableStringFixer;
use App\Data\Fixer\String\GenericStringFixer;
use App\Utils\Regexp\Pattern;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class LanguagesFixer extends AbstractListFixer
{
    private readonly Pattern $replacementPattern;
    private readonly ConfigurableStringFixer $fixer;

    /**
     * @param array{replacements: array<string, string>, regex_prefix: string, regex_suffix: string, regexp: string} $languages
     */
    public function __construct(
        #[Autowire(param: 'languages')] array $languages,
        private readonly GenericStringFixer $genericStringFixer,
    ) {
        $this->replacementPattern = new Pattern($languages['regexp'], 'i');

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

        $match = $this->replacementPattern->match($subject);

        if (null === $match->matches['language']) {
            return $subject;
        }

        $language = mb_ucfirst($match->matches['language']);
        $limited = null !== $match->matches['prefix'] || null !== $match->matches['suffix'];

        return $language.($limited ? ' (limited)' : '');
    }
}
