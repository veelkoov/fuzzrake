<?php

declare(strict_types=1);

namespace App\Controller\IuForm\Utils;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class IuSession
{
    private readonly string $prefix;

    public function __construct(
        private readonly SessionInterface $session,
        string $makerId,
    ) {
        $this->prefix = "iu_form_{$makerId}_";

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

    private function assureFreshLifetime(): void
    {
        $refreshThreshold = (int) ($this->session->getMetadataBag()->getLifetime() / 24);

        if (time() > $this->session->getMetadataBag()->getCreated() + $refreshThreshold) {
            $this->session->getMetadataBag()->stampNew(); // Refresh "get created" timestamp
            $this->session->migrate(); // This sends a new cookie, possibly there's a better way?
        }
    }
}
