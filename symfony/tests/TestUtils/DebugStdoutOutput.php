<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use Symfony\Component\Console\Output\Output;

class DebugStdoutOutput extends Output
{
    protected function doWrite(string $message, bool $newline): void
    {
        echo $message.($newline ? PHP_EOL : '');
    }
}
