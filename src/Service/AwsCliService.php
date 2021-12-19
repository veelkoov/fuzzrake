<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class AwsCliService
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(array $commandArgv, string $commandDescription): bool
    {
        $process = new Process($commandArgv);
        $process->run();

        $context = [
            'stdout' => $process->getOutput(),
            'stderr' => $process->getErrorOutput(),
        ];

        if ($process->isSuccessful()) {
            $this->logger->info("$commandDescription successful", $context);

            return true;
        } else {
            $this->logger->error("$commandDescription failed", $context);

            return false;
        }
    }
}
