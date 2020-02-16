<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\RequestStack;

class HostsService
{
    private RequestStack $requestStack;
    private array $hosts;
    private string $environment;

    public function __construct(RequestStack $requestStack, array $hosts, string $environment)
    {
        $this->requestStack = $requestStack;
        $this->hosts = $hosts;
        $this->environment = $environment;
    }

    public function isDevMachine(): bool
    {
        return in_array($this->environment, ['dev', 'test']) && '127.0.0.1' === $this->requestStack->getMasterRequest()->getClientIp();
    }

    public function isProduction(): bool
    {
        return $this->getHostname() === $this->hosts['production'];
    }

    private function getHostname(): string
    {
        try {
            return $this->requestStack->getMasterRequest()->getHost();
        } catch (SuspiciousOperationException $e) {
            return 'unknown/error';
        }
    }
}
