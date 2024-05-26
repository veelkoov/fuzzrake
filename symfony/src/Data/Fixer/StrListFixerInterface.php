<?php

declare(strict_types=1);

namespace App\Data\Fixer;

interface StrListFixerInterface
{
    /**
     * @param list<string> $subject
     *
     * @return list<string>
     */
    public function fix(array $subject): array;
}
