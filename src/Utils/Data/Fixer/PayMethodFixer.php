<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use TRegx\CleanRegex\Pattern;

class PayMethodFixer extends AbstractListFixer
{
    private Pattern $nsp;

    public function __construct(array $paymentMethods, array $strings)
    {
        parent::__construct($paymentMethods, $strings);

        $this->nsp = pattern('\([^)]+\)');
    }

    protected static function shouldSort(): bool
    {
        return false;
    }

    protected static function getSeparatorRegexp(): string
    {
        return '[\n,.]|[, ]and ';
    }

    protected function getNonsplittable(string $subject): array
    {
        return [
            'wise.com',
            ...$this->nsp->match($subject)->all(),
        ];
    }
}
