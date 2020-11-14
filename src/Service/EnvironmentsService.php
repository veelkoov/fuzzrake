<?php

declare(strict_types=1);

namespace App\Service;

class EnvironmentsService
{
    private string $environment;

    public function __construct(string $environment)
    {
        $this->environment = $environment;
    }

    public function isDevOrTest(): bool
    {
        return in_array($this->environment, ['dev', 'test']);
    }

    public function isTest(): bool
    {
        return 'test' === $this->environment;
    }

    public function isDev(): bool
    {
        return 'dev' === $this->environment;
    }
}
