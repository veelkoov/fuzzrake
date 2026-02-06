<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Data\AnalysisInput;
use App\Utils\Creator\SmartAccessDecorator as Creator;
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

    /**
     * @param string|Stringable $message
     * @param array<mixed>      $context
     */
    #[Override]
    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, array_merge($this->context, $context));
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

    public function resetContextFor(AnalysisInput|Creator $input): void
    {
        $this->clearContext();

        if ($input instanceof Creator) {
            $this->addContext('creator', $input->getLastCreatorId());
        } else {
            $this->addContext('creator', $input->creator->getLastCreatorId());
            $this->addContext('url', $input->url);
        }
    }
}
