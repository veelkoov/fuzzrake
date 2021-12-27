<?php

declare(strict_types=1);

namespace App\Controller\IuFormUtils;

use Symfony\Component\HttpFoundation\Session\Session;

class Steps
{
    private const PREFIX = 'iu_form_';
    private const CAPTCHA_DONE = 'captcha_done';

    public function __construct(
        private readonly Session $session,
    ) {
    }

    public function captchaDone(): bool
    {
        return $this->getFromSession(self::CAPTCHA_DONE, false);
    }

    public function markCaptchaDone(): void
    {
        $this->setInSession(self::CAPTCHA_DONE, true);
    }

    public function reset(): void
    {
        $this->resetInSession(self::CAPTCHA_DONE);
    }

    private function getFromSession(string $key, bool $default): bool
    {
        return $this->session->get(self::PREFIX.$key, $default);
    }

    private function setInSession(string $key, bool $value): void
    {
        $this->session->set(self::PREFIX.$key, $value);
    }

    private function resetInSession(string $key): void
    {
        $this->session->remove(self::PREFIX.$key);
    }
}
