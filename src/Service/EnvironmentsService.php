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

    public function isDevMachine(): bool
    {
        return in_array($this->environment, ['dev', 'test']);
    }

    public function isProduction(): bool
    {
        return 'prod' === $this->environment;
    }
}
