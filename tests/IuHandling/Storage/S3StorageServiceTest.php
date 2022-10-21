<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Storage;

use App\IuHandling\Storage\LocalStorageService;
use App\IuHandling\Storage\S3StorageService;
use App\Service\AwsCliService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class S3StorageServiceTest extends TestCase
{
    /**
     * @dataProvider regexDataProvider
     */
    public function testRegex(string $s3BucketAddress, bool $exceptionExpected): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $localMock = $this->createMock(LocalStorageService::class);
        $cliMock = $this->createMock(AwsCliService::class);

        try {
            $result = new S3StorageService($loggerMock, $localMock, $cliMock, $s3BucketAddress);
        } catch (InvalidArgumentException $exception) {
            $result = $exception;
        }

        self::assertInstanceOf($exceptionExpected ? InvalidArgumentException::class : S3StorageService::class, $result);
    }

    /**
     * @return array<array{string, bool}>
     */
    public function regexDataProvider(): array
    {
        return [
            ['', false],
            ['s3://some-s3-bucket-name', false],
            ['s3://some-s3-b/ucket-name', true],
            ['s3://some-s3-b/ucket-name ', true],
        ];
    }
}
