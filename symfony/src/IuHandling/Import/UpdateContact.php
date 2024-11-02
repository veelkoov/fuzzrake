<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\ContactPermit;
use App\Utils\Artisan\SmartAccessDecorator as Creator;

final readonly class UpdateContact
{
    public function __construct(
        public string $description,
        public bool $isAllowed,
        public string $address,
    ) {
    }

    public static function from(Creator $original, Creator $updated): self
    {
        $was = self::getContactAllowed($original);
        $now = self::getContactAllowed($updated);
        $isNew = null === $original->getId();

        $description = $isNew || $was === $now ? $now->getLabel() : "{$was->getLabel()} → {$now->getLabel()}";
        $isAllowed = ($isNew || ContactPermit::NO !== $was) && (ContactPermit::NO !== $now);

        $address = !$isAllowed
            ? ''
            : ($isNew
                ? $updated->getEmailAddress()
                : $original->getEmailAddress());

        return new self(
            $description,
            $isAllowed,
            $address,
        );
    }

    private static function getContactAllowed(Creator $creator): ContactPermit
    {
        return $creator->getContactAllowed() ?? ContactPermit::NO;
    }
}
