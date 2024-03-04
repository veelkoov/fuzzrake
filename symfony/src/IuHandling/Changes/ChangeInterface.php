<?php

declare(strict_types=1);

namespace App\IuHandling\Changes;

use App\Data\Definitions\Fields\Field;

interface ChangeInterface
{
    public function getDescription(): string;

    public function isActuallyAChange(): bool;

    public function getField(): Field;
}
