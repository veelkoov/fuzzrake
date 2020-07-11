<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Artisan\ContactPermit;

class ContactAllowedFixer implements FixerInterface
{
    private ContactPermit $contactPermit;

    public function __construct(ContactPermit $contactPermit)
    {
        $this->contactPermit = $contactPermit;
    }

    public function fix(string $fieldName, string $subject): string
    {
        return str_replace($this->contactPermit->getValues(), $this->contactPermit->getKeys(), $subject);
    }
}
