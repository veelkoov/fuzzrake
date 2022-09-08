<?php

declare(strict_types=1);

namespace App\IuHandling;

use App\Service\AwsCliService;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class S3StorageService
{
    private readonly string $s3CopiesBucketUrl;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LocalStorageService $local,
        private readonly AwsCliService $cli,
        #[Autowire('%env(resolve:S3_COPIES_BUCKET_URL)%')]
        string $s3CopiesBucketUrl,
    ) {
        $this->s3CopiesBucketUrl = $s3CopiesBucketUrl;
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
