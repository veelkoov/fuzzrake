<?php

declare(strict_types=1);

namespace App\Controller\IuForm\Utils;

use App\Data\Definitions\ContactPermit;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class IuState
{
    private const string CAPTCHA_DONE = 'captcha_done';

    public readonly string $previousPassword;
    public readonly bool $wasContactAllowed;

    private readonly IuSession $session;

    public function __construct(
        SessionInterface $session,
        public readonly ?string $makerId,
        public readonly Artisan $artisan,
    ) {
        $this->session = new IuSession($session, $makerId ?? '');
        $this->previousPassword = $artisan->getPassword();
        $this->wasContactAllowed = ContactPermit::isAtLeastCorrections($artisan->getContactAllowed());
    }

    public function isNew(): bool
    {
        return null == $this->makerId;
    }

    public function captchaDone(): bool
    {
        return $this->session->isDone(self::CAPTCHA_DONE);
    }

    public function markCaptchaDone(): void
    {
        $this->session->markDone(self::CAPTCHA_DONE);
    }
}
