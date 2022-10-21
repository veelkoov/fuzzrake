<?php

declare(strict_types=1);

namespace App\Tests\Service\Notifications;

use App\Service\AwsCliService;
use App\Service\EnvironmentsService;
use App\Service\Notifications\SnsService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SnsServiceTest extends TestCase
{
    /**
     * @dataProvider regexDataProvider
     */
    public function testRegex(string $snsTopicArn, bool $exceptionExpected): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $cliMock = $this->createMock(AwsCliService::class);
        $envsMock = $this->createMock(EnvironmentsService::class);

        try {
            $result = new SnsService($loggerMock, $cliMock, $snsTopicArn, $envsMock);
        } catch (InvalidArgumentException $exception) {
            $result = $exception;
        }

        self::assertInstanceOf($exceptionExpected ? InvalidArgumentException::class : SnsService::class, $result);
    }

    /**
     * @return array<array{string, bool}>
     */
    public function regexDataProvider(): array
    {
        return [
            ['', false],
            ['arn:aws:sns:us-east-1:123456789012:Some_Topic_Name', false],
            ['arn:aws:sns::us-east-1:123456789012:Some_Topic_Name', true],
            ['arn:aws:sns:us-east-1:123456789012:Some_Topic_Name ', true],
        ];
    }
}
