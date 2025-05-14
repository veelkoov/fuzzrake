<?php

declare(strict_types=1);

namespace App\Data\Fixer\String;

use App\Data\Fixer\StringFixerInterface;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class UrlFixerGeneric implements StringFixerInterface
{
    private readonly ConfigurableStringFixer $fixer;

    /**
     * @param psFixerConfig $urls
     */
    public function __construct(
        #[Autowire(param: 'urls')] array $urls,
        private readonly GenericStringFixer $genericStringFixer,
    ) {
        $this->fixer = new ConfigurableStringFixer($urls);
    }

    #[Override]
    public function fix(string $subject): string
    {
        return $this->fixer->fix($this->genericStringFixer->fix($subject));
    }
}
