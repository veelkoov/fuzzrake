<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\String\ConfigurableStringFixer;
use App\Data\Fixer\String\GenericStringFixer;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class PayPlanFixer extends AbstractListFixer
{
    private readonly ConfigurableStringFixer $fixer;

    /**
     * @param psFixerConfig $noPayPlans
     */
    public function __construct(
        #[Autowire(param: 'noPayPlans')] array $noPayPlans,
        private readonly GenericStringFixer $genericStringFixer,
    ) {
        $this->fixer = new ConfigurableStringFixer($noPayPlans);
    }

    #[Override]
    protected function getSeparatorRegexp(): ?string
    {
        return null;
    }

    #[Override]
    protected function fixItem(string $subject): string
    {
        return $this->fixer->fix($this->genericStringFixer->fix($subject));
    }
}
