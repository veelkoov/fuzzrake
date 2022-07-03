<?php

declare(strict_types=1);

namespace App\Controller\IuForm\Utils;

use App\DataDefinitions\ContactPermit;
use App\DataDefinitions\Fields\Fields;
use App\DataDefinitions\Fields\SecureValues;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\SafeArrayRead;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class IuState
{
    private const CAPTCHA_DONE = 'captcha_done';
    private const DATA_DONE = 'data_done';

    public readonly string $previousPassword;
    public readonly bool $wasContactAllowed;

    private readonly IuSession $session;
    private bool $hasRestoreErrors = false;

    public function __construct(
        private readonly LoggerInterface $logger,
        SessionInterface $session,
        public readonly ?string $makerId,
        public readonly Artisan $artisan,
    ) {
        $this->session = new IuSession($session, $makerId ?? '');
        $this->previousPassword = $artisan->getPassword();
        $this->wasContactAllowed = ContactPermit::NO !== $artisan->getContactAllowed();

        $this->restoreState();
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

    public function getStarted(): ?DateTimeImmutable
    {
        return $this->session->getStarted();
    }

    public function save(): void
    {
        $data = $this->artisan->getAllData();
        SecureValues::forSessionStorage($data);

        $this->session->save($data);
    }

    public function hasRestoreErrors(): bool
    {
        return $this->hasRestoreErrors;
    }

    private function restoreState(): void
    {
        $saved = $this->session->getSaved();

        if (null === $saved) {
            $this->logger->info('No data to restore.', $this->getLogContext());

            return;
        }

        $result = new SafeArrayRead($saved, $this->artisan, Fields::inIuForm());

        if ([] !== $result->getErrors()) {
            $this->hasRestoreErrors = true;

            $savedData = $this->session->getSaved();
            SecureValues::forLogs($savedData);

            $this->logger->info('Tried to restore data in given context.', $this->getLogContext(['savedData' => $savedData]));
        }

        foreach ($result->getErrors() as $error) {
            $this->logger->error("Restore error: $error", $this->getLogContext());
        }
    }

    /**
     * @param mixed[] $additionalContext
     *
     * @return mixed[]
     */
    private function getLogContext(array $additionalContext = []): array
    {
        return array_merge(['iu_session' => $this->session->getId()], $additionalContext);
    }
}
