<?php

declare(strict_types=1);

namespace App\Data\Fixer\String;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class GenericStringFixer extends ConfigurableStringFixer
{
    /**
     * @param psFixerConfig $strings
     */
    public function __construct(
        #[Autowire(param: 'strings')] array $strings,
    ) {
        parent::__construct($strings);
    }
}
