<?php

declare(strict_types=1);

namespace App\Utils\Data\Validator;

use App\Utils\Artisan\Field;

interface ValidatorInterface
{
    public function isValid(Field $field, $subject): bool;
}
