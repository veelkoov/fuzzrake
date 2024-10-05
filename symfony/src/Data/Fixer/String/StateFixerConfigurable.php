<?php

declare(strict_types=1);

namespace App\Data\Fixer\String;

use App\Data\Fixer\StringFixerInterface;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class StateFixerConfigurable implements StringFixerInterface
{
    private readonly ConfigurableStringFixer $fixer;

    /**
     * @param psFixerConfig $states
     */
    public function __construct(
        #[Autowire(param: 'states')] array $states,
        private readonly GenericStringFixer $genericStringFixer,
    ) {
        $this->fixer = new ConfigurableStringFixer($states);
    }

    #[Override]
    public function fix(string $subject): string
    {
        return $this->fixer->fix($this->genericStringFixer->fix($subject));
    }
}
