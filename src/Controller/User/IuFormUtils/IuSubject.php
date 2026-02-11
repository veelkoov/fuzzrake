<?php

declare(strict_types=1);

namespace App\Controller\User\IuFormUtils;

use App\Data\Definitions\ContactPermit;
use App\Utils\Creator\SmartAccessDecorator as Creator;

final readonly class IuSubject
{
    public string $previousPassword;
    public string $previousEmailAddress;
    public bool $wasContactAllowed;
    public bool $isNew;

    public function __construct(
        public ?string $creatorId,
        public Creator $creator,
    ) {
        $this->previousPassword = $creator->getPassword();
        $this->previousEmailAddress = $this->creator->getEmailAddress();
        $this->wasContactAllowed = ContactPermit::isAtLeastCorrections($creator->getContactAllowed());
        $this->isNew = null === $this->creatorId;
    }
}
