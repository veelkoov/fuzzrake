<?php

declare(strict_types=1);

namespace App\Data\Fixer\String;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CountryFixer extends ConfigurableStringFixer
{
    /**
     * @param psFixerConfig $countries
     */
    public function __construct(
        #[Autowire(param: 'country_fixes')] array $countries,
    ) {
        parent::__construct($countries);
    }
}
