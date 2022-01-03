<?php

declare(strict_types=1);

namespace App\Controller\IuFormUtils;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

class IuSession
{
    private readonly string $prefix;
    private readonly string $keySavedData;
    private readonly string $keySessionId;

    private readonly UuidV4 $uuid;

    public function __construct(
        private readonly Session $session,
        string $makerId,
    ) {
        $this->prefix = "iu_form_{$makerId}_";
        $this->keySavedData = $this->prefix.'saved_data';
        $this->keySessionId = $this->prefix.'session_uuid';

        $stringUuid = $this->session->get($this->keySessionId);

        if (is_string($stringUuid) && UuidV4::isValid($stringUuid)) {
            $this->uuid = UuidV4::fromRfc4122($stringUuid);
        } else {
            $this->uuid = Uuid::v4();

            $this->session->set($this->keySessionId, $this->uuid->toRfc4122());
        }
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
}
