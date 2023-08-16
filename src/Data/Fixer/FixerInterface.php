<?php

declare(strict_types=1);

namespace App\Data\Fixer;

interface FixerInterface
{
    public function fix(string $subject): string;
}
