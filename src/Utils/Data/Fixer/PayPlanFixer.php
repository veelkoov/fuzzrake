<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Replacements;

class PayPlanFixer extends AbstractStringFixer
{
    private readonly Replacements $replacements;

    public function __construct(array $noPayPlans, array $strings)
    {
        parent::__construct($strings);

        $this->replacements = new Replacements($noPayPlans['replacements'], 'i', $noPayPlans['commonRegexPrefix'], $noPayPlans['commonRegexSuffix']);
    }

    public function fix(string $subject): string
    {
        $result = parent::fix($subject);

        return $this->replacements->do($result);
    }
}
