<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\ContactPermit;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Contact;

class UpdateContact
{
    public function __construct(
        public readonly string $description,
        public readonly bool $isAllowed,
        public readonly string $method,
        public readonly string $address,
        public readonly bool $isEmail,
    ) {
    }

    public static function from(Artisan $original, Artisan $updated): self
    {
        $was = self::getContactAllowed($original);
        $now = self::getContactAllowed($updated);
        $isNew = null === $original->getId();

        $description = $isNew || $was === $now ? $now->getLabel() : "{$was->getLabel()} → {$now->getLabel()}";
        $isAllowed = ($isNew || ContactPermit::NO !== $was) && (ContactPermit::NO !== $now);

        if ($isNew) {
            $method = $updated->getContactMethod();
            $address = $updated->getContactAddressPlain();
        } else {
            $method = $original->getContactMethod();
            $address = $original->getContactAddressPlain();
        }

        return new self(
            $description,
            $isAllowed,
            $method,
            $address,
            Contact::E_MAIL === $method,
        );
    }

    private static function getContactAllowed(Artisan $artisan): ContactPermit
    {
        return $artisan->getContactAllowed() ?? ContactPermit::NO;
    }
}
