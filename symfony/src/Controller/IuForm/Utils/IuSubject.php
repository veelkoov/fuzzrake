<?php

declare(strict_types=1);

namespace App\Controller\IuForm\Utils;

use App\Data\Definitions\ContactPermit;
use App\Utils\Artisan\SmartAccessDecorator as Creator;

final readonly class IuSubject
{
    public string $previousPassword;
    public bool $wasContactAllowed;
    public bool $isNew;

    public function __construct(
        public ?string $makerId,
        public Creator $creator,
    ) {
        $this->previousPassword = $creator->getPassword();
        $this->wasContactAllowed = ContactPermit::isAtLeastCorrections($creator->getContactAllowed());
        $this->isNew = null === $this->makerId;
    }
}
