<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\RequestStack;

class HostsService
{
    /**
     * @var array
     */
    private $hosts;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack, array $hosts)
    {
        $this->hosts = $hosts;
        $this->requestStack = $requestStack;
    }

    public function isDevMachine(): bool
    {
        return '127.0.0.1' === $this->requestStack->getMasterRequest()->getClientIp();
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
