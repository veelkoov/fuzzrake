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

        switch ($method) {
            case ContactParser::INVALID:
                $obfuscated = 'PLEASE CORRECT';
                break;

            case '':
                $obfuscated = '';
                break;

            default:
                $obfuscated = $method.': '.Utils::obscureContact($address);
                break;
        }

        $artisan->setContactMethod($method)
            ->setContactAddressObfuscated($obfuscated)
            ->getPrivateData()
            ->setOriginalContactInfo($newOriginalContactValue)
            ->setContactAddress($address);
    }
}
