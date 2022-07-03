<?php

declare(strict_types=1);

namespace App\Controller\IuForm\Utils;

use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

class IuSession
{
    private readonly string $prefix;
    private readonly string $keySavedData;
    private readonly string $keySessionId;
    private readonly string $keyStartDateTime;

    private readonly UuidV4 $uuid;

    public function __construct(
        private readonly SessionInterface $session,
        string $makerId,
    ) {
        $this->prefix = "iu_form_{$makerId}_";
        $this->keySavedData = $this->prefix.'saved_data';
        $this->keySessionId = $this->prefix.'session_uuid';
        $this->keyStartDateTime = $this->prefix.'start_datetime';

        $stringUuid = $this->session->get($this->keySessionId);

        if (is_string($stringUuid) && UuidV4::isValid($stringUuid)) {
            $this->uuid = UuidV4::fromRfc4122($stringUuid);
        } else {
            $this->uuid = Uuid::v4(); // @phpstan-ignore-line

            $this->session->set($this->keySessionId, $this->uuid->toRfc4122());
            $this->session->set($this->keyStartDateTime, UtcClock::now());
        }

        $this->assureFreshLifetime();
    }

    public function isDone(string $key): bool
    {
        $result = $this->session->get("{$this->prefix}{$key}", false);

        return is_bool($result) ? $result : false;
    }

    public function markDone(string $key): void
    {
        $this->session->set("{$this->prefix}{$key}", true);
    }

    public function reset(): void
    {
        foreach ($this->session->all() as $name => $_) {
            if (str_starts_with($name, $this->prefix)) {
                $this->session->remove($name);
            }
        }
    }

    public function save(array $data): void
    {
        $this->session->set($this->keySavedData, $data);
    }

    public function getSaved(): mixed
    {
        return $this->session->get($this->keySavedData);
    }

    public function getId(): string
    {
        return $this->uuid->toRfc4122();
    }

    public function getStarted(): ?DateTimeImmutable
    {
        $result = $this->session->get($this->keyStartDateTime);

        return $result instanceof DateTimeImmutable ? $result : null;
    }

    private function assureFreshLifetime(): void
    {
        $refreshThreshold = (int) ($this->session->getMetadataBag()->getLifetime() / 24);

        if (time() > $this->session->getMetadataBag()->getCreated() + $refreshThreshold) {
            $this->session->getMetadataBag()->stampNew(); // Refresh "get created" timestamp
            $this->session->migrate(); // This sends a new cookie, possibly there's a better way?
        }
    }
}
