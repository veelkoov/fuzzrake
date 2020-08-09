<?php

declare(strict_types=1);

namespace App\Service;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class IuFormS3Service
{
    private LoggerInterface $logger;
    private IuFormSubmissionLocalService $local;
    private AwsCliService $cli;
    private string $s3CopiesBucketUrl;

    public function __construct(LoggerInterface $logger, IuFormSubmissionLocalService $local, AwsCliService $cli, string $iuFormDataS3CopiesBucketUrl)
    {
        $this->logger = $logger;
        $this->local = $local;
        $this->cli = $cli;
        $this->s3CopiesBucketUrl = $iuFormDataS3CopiesBucketUrl;

        if (pattern('^(s3://[-.a-z0-9]+)?$', 'i')->fails($iuFormDataS3CopiesBucketUrl)) {
            throw new InvalidArgumentException("$iuFormDataS3CopiesBucketUrl is not a valid S3 bucket URL");
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
