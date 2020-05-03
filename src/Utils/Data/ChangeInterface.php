<?php

declare(strict_types=1);

namespace App\Utils\Data;

interface ChangeInterface
{
    public function getDescription(): string;

    public function isActuallyAChange(): bool;
}
