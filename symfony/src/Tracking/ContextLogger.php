<?php

declare(strict_types=1);

namespace App\Tracking;

use Override;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;

class ContextLogger extends AbstractLogger
{
    /** @var array<string, mixed> */
    public private(set) array $context = [];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function addContext(string $key, mixed $value, bool $clearAllPrevious = false): void
    {
        if ($clearAllPrevious) {
            $this->clearContext();
        }

        $this->context[$key] = $value;
    }

    public function clearContext(): void
    {
        $this->context = [];
    }

    /**
     * @param string|Stringable $message
     * @param array<mixed>      $context
     */
    #[Override]
    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, array_merge($this->context, $context));
    }
}
