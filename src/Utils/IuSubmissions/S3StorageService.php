<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Service\AwsCliService;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class S3StorageService
{
    public function __construct(
        private LoggerInterface $logger,
        private LocalStorageService $local,
        private AwsCliService $cli,
        private string $s3CopiesBucketUrl,
    ) {
        if (pattern('^(s3://[-.a-z0-9]+)?$', 'i')->fails($s3CopiesBucketUrl)) {
            throw new InvalidArgumentException("$s3CopiesBucketUrl is not a valid S3 bucket URL");
        }
    }

    public function sendCopyToS3(string $relativeFilePath): bool
    {
        if ('' === $this->s3CopiesBucketUrl) {
            $this->logger->warning('Unable to send data to S3 - the URL is not configured');

            return false;
        }

        return $this->cli->execute(['aws', 's3', 'cp', $this->local->getAbsolutePath($relativeFilePath),
            $this->s3CopiesBucketUrl.'/'.$relativeFilePath, ], 'Sending copy to S3');
    }
}
