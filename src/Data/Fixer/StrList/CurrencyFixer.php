<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\String\ConfigurableStringFixer;
use App\Data\Fixer\String\GenericStringFixer;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CurrencyFixer extends AbstractListFixer
{
    private readonly ConfigurableStringFixer $fixer;

    /**
     * @param psFixerConfig $currencies
     */
    public function __construct(
        #[Autowire(param: 'currencies')] array $currencies,
        private readonly GenericStringFixer $genericStringFixer,
    ) {
        $this->fixer = new ConfigurableStringFixer($currencies);
    }

    #[Override]
    protected function fixItem(string $subject): string
    {
        return $this->fixer->fix(strtoupper($this->genericStringFixer->fix($subject)));
    }
}
