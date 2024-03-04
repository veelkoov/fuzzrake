<?php

declare(strict_types=1);

namespace App\Data\Fixer;

use App\Utils\Regexp\Replacements;

class PayPlanFixer extends AbstractStringFixer
{
    private readonly Replacements $replacements;

    /**
     * @param psFixerConfig $noPayPlans
     * @param psFixerConfig $strings
     */
    public function __construct(array $noPayPlans, array $strings)
    {
        parent::__construct($strings);

        $this->replacements = new Replacements($noPayPlans['replacements'], 'i', $noPayPlans['regex_prefix'], $noPayPlans['regex_suffix']);
    }

    public function fix(string $subject): string
    {
        $result = parent::fix($subject);

        return $this->replacements->do($result);
    }
}
