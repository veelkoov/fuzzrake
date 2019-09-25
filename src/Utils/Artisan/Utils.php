<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Entity\Artisan;
use App\Utils\Contact;

class Utils
{
    private function __construct()
    {
    }

    public static function updateContact(Artisan $artisan, string $newOriginalContactValue): void
    {
        list($method, $address) = Contact::parse($newOriginalContactValue);

        switch ($method) {
            case Contact::INVALID:
                $obfuscated = 'PLEASE CORRECT';
                break;

            case '':
                $obfuscated = '';
                break;

            default:
                $obfuscated = $method.': '.Contact::obscure($address);
                break;
        }

        $artisan->setContactMethod($method)
            ->setContactInfoObfuscated($obfuscated)
            ->getPrivateData()
            ->setOriginalContactInfo($newOriginalContactValue)
            ->setContactAddress($address);
    }
}
