<?php

declare(strict_types=1);

namespace App\Controller\IuFormUtils;

use App\DataDefinitions\ContactPermit;
use App\DataDefinitions\Fields\Fields;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Data\SafeArrayRead;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use TypeError;

class IuState
{
    private const CAPTCHA_DONE = 'captcha_done';
    private const DATA_DONE = 'data_done';

    public readonly string $previousPassword;
    public readonly bool $wasContactAllowed;

    private readonly IuSession $session;

    public function __construct(
        SessionInterface $session,
        public readonly ?string $makerId,
        public readonly Artisan $artisan,
    ) {
        $this->session = new IuSession($session);
        $this->previousPassword = $artisan->getPassword();
        $this->wasContactAllowed = ContactPermit::NO !== $artisan->getContactAllowed();
    }

    public function isNew(): bool
    {
        return null == $this->makerId;
    }

    public function captchaDone(): bool
    {
        return $this->session->isDone(self::CAPTCHA_DONE);
    }

    public function dataDone(): bool
    {
        return $this->session->isDone(self::DATA_DONE);
    }

    public function markCaptchaDone(): void
    {
        $this->session->markDone(self::CAPTCHA_DONE);
    }

    public function markDataDone(): void
    {
        $this->session->markDone(self::DATA_DONE);
    }

    public function reset(): void
    {
        $this->session->reset();
    }

    public function save(): void
    {
        $this->session->save($this->artisan->getAllData());
    }

    public function restore(): ?SafeArrayRead
    {
        $saved = $this->session->getSaved();

        if (null === $saved) {
            return null;
        }

        return SafeArrayRead::copy($saved, $this->artisan);
    }
}
