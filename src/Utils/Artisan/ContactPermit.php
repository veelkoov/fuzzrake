<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

class ContactPermit extends Dictionary
{
    public const NO = 'NO'; // grep-contact-permit-no

    public function getAttributeKey(): string
    {
        return 'contact_permit';
    }
}
