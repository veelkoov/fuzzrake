<?php

declare(strict_types=1);

namespace App\IuHandling\Changes;

use App\DataDefinitions\Fields\Field;

interface ChangeInterface
{
    public function getDescription(): string;

    public function isActuallyAChange(): bool;

    public function getField(): Field;
}
