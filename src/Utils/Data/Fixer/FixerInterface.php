<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

interface FixerInterface
{
    public function fix(string $fieldName, string $subject): string;
}
