<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;

class ArtisanUtils
{
    private function __construct()
    {
    }

    public static function updateContact(Artisan $artisan, string $newOriginalContactValue): void
    {
        list($method, $address) = ContactParser::parse($newOriginalContactValue);

        $obfuscated = '' === $method || ContactParser::INVALID === $method ? '' : Utils::obscureContact($address);

        $artisan->setContactMethod($method)
            ->setContactAddressObfuscated($method && $obfuscated ? "$method: $obfuscated" : '') // FIXME
            ->getPrivateData()
            ->setOriginalContactInfo($newOriginalContactValue)
            ->setContactAddress($address);
    }
}
