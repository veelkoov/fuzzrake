<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Artisan\ContactPermit;

class ContactAllowedFixer implements FixerInterface
{
    public function fix(string $fieldName, string $subject): string
    {
        return str_replace(ContactPermit::getValues(), ContactPermit::getKeys(), $subject);
    }
}
