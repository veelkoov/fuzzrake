<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\DataDefinitions\ContactPermit;

class ContactAllowedFixer implements FixerInterface
{
    public function fix(string $subject): string
    {
        return str_replace(ContactPermit::getValues(), ContactPermit::getKeys(), $subject);
    }
}
