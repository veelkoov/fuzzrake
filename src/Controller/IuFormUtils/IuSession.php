<?php

declare(strict_types=1);

namespace App\Controller\IuFormUtils;

use Symfony\Component\HttpFoundation\Session\Session;

class IuSession
{
    private const PREFIX = 'iu_form_';

    public function __construct(
        private readonly Session $session,
    ) {
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
        $this->session->set(self::PREFIX.'saved_data', $data);
    }

    public function getSaved(): mixed
    {
        $result = $this->session->get(self::PREFIX.'saved_data');

        return $result;
    }
}
