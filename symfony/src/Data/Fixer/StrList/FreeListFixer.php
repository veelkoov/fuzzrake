<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\String\ConfigurableStringFixer;
use App\Data\Fixer\String\GenericStringFixer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class FreeListFixer extends AbstractListFixer
{
    private readonly ConfigurableStringFixer $fixer;

    /**
     * @param psFixerConfig $lists
     */
    public function __construct(
        #[Autowire(param: 'lists')] array $lists,
        private readonly GenericStringFixer $genericStringFixer,
    ) {
        $this->fixer = new ConfigurableStringFixer($lists);
    }

    protected function getSeparatorRegexp(): ?string
    {
        return null;
    }

    protected function fixItem(string $subject): string
    {
        return $this->fixer->fix($this->genericStringFixer->fix($subject));
    }
}
