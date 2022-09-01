<?php

declare(strict_types=1);

namespace App\Submissions\Changes;

interface ChangeInterface
{
    public function getDescription(): string;

    public function isActuallyAChange(): bool;
}
