<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Component\Filesystem\Filesystem;

trait FilesystemTrait
{
    public const string SAFE_DEFAULT_VALUE = '/tmp';

    /**
     * @var non-empty-string
     */
    protected string $testsTempDir = self::SAFE_DEFAULT_VALUE;

    #[Before]
    protected function setUpTemporaryDirectory(): void
    {
        $this->testsTempDir = $this->getTempDirForTestsUnsafe();
    }

    #[After]
    protected function cleanupTemporaryDirectory(): void
    {
        if (self::SAFE_DEFAULT_VALUE !== $this->testsTempDir) {
            new Filesystem()->remove($this->testsTempDir);
        }
    }

    /**
     * @return non-empty-string
     */
    private function getTempDirForTestsUnsafe(): string
    {
        $filesystem = new Filesystem();

        $result = $filesystem->tempnam(sys_get_temp_dir(), 'fuzzrake-tests-');
        $filesystem->remove($result);
        // Race condition; ignore; use this method only in tests.
        $filesystem->mkdir($result, 0700);

        return $result; // @phpstan-ignore return.type (It is non-empty)
    }
}
