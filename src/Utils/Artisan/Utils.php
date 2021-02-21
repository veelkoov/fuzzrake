<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Entity\Artisan;
use App\Utils\Contact;
use App\Utils\Traits\UtilityClass;

final class Utils
{
    use UtilityClass;

    public static function updateContact(Artisan $artisan, string $newOriginalContactValue): void
    {
        [$method, $address] = Contact::parse($newOriginalContactValue);

        $obfuscated = match ($method) {
            Contact::INVALID => 'PLEASE CORRECT',
            ''               => '',
            default          => $method.': '.Contact::obscure($address),
        };

        $artisan->setContactMethod($method)
            ->setContactInfoObfuscated($obfuscated)
            ->getPrivateData()
            ->setOriginalContactInfo($newOriginalContactValue)
            ->setContactAddress($address);
    }
}
