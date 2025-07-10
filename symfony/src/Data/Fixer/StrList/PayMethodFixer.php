<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\String\ConfigurableStringFixer;
use App\Data\Fixer\String\GenericStringFixer;
use App\Utils\PackedStringList;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TRegx\CleanRegex\Pattern;

final class PayMethodFixer extends AbstractListFixer
{
    private readonly Pattern $nsp;
    private readonly ConfigurableStringFixer $fixer;

    /**
     * @param psFixerConfig $paymentMethods
     */
    public function __construct(
        #[Autowire(param: 'paymentMethods')] array $paymentMethods,
        private readonly GenericStringFixer $genericStringFixer,
    ) {
        $this->fixer = new ConfigurableStringFixer($paymentMethods);
        $this->nsp = Pattern::of('\([^)\n]+\)');
    }

    #[Override]
    protected function getNonsplittable(array $subject): array
    {
        $joinedSubject = PackedStringList::pack($subject); // Cheap and lame

        return array_values([
            'wise.com',
            'boosty.to',
            ...$this->nsp->search($joinedSubject)->all(),
        ]);
    }

    #[Override]
    protected function fixItem(string $subject): string
    {
        return $this->fixer->fix($this->genericStringFixer->fix($subject));
    }
}
