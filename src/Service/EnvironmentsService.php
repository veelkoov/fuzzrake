<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EnvironmentsService
{
    private readonly string $environment;

    public function __construct(
        #[Autowire('%kernel.environment%')]
        string $environment,
    ) {
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
