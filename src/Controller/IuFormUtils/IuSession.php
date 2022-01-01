<?php

declare(strict_types=1);

namespace App\Controller\IuFormUtils;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

class IuSession
{
    private const PREFIX = 'iu_form_';
    private const KEY_SAVED_DATA = self::PREFIX.'saved_data';
    private const KEY_SESSION_ID = self::PREFIX.'session_id';

    public function __construct(
        private readonly Session $session,
    ) {
        $this->getId(); // Force I/U session ID initiation
    }

    public function isDone(string $key): bool
    {
        $result = $this->session->get(self::PREFIX.$key, false);

        return is_bool($result) ? $result : false;
    }

    public function markDone(string $key): void
    {
        $this->session->set(self::PREFIX.$key, true);
    }

    public function reset(): void
    {
        foreach ($this->session->all() as $name => $_) {
            if (str_starts_with($name, self::PREFIX)) {
                $this->session->remove($name);
            }
        }
    }

    public function save(array $data): void
    {
        $this->session->set(self::KEY_SAVED_DATA, $data);
    }

    public function getSaved(): mixed
    {
        return $this->session->get(self::KEY_SAVED_DATA);
    }

    public function getId(): string
    {
        $id = $this->session->get(self::KEY_SESSION_ID);

        if (!is_string($id) || UuidV4::isValid($id)) {
            $id = Uuid::v4()->toRfc4122();

            $this->session->set(self::KEY_SESSION_ID, $id);
        }

        return $id;
    }
}
