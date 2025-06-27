<?php

declare(strict_types=1);

namespace App\Utils\Creator\Changes;

interface ChangeInterface
{
    public function getDescription(): string;

    public function isActuallyAChange(): bool;
}
