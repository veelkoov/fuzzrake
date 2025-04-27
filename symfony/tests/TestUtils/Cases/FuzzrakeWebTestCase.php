<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use App\Tests\TestUtils\Cases\Traits\UtilsTrait;
use App\Utils\TestUtils\TestsBridge;
use LogicException;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FuzzrakeWebTestCase extends WebTestCase
{
    use EntityManagerTrait;
    use UtilsTrait;

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        TestsBridge::reset();
    }

    /**
     * @param array<string, string> $options
     * @param array<string, string> $server
     */
    #[Override]
    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        $result = parent::createClient($options, $server);

        self::resetDB();

        return $result;
    }

    /**
     * @param literal-string $value
     */
    protected function getCaptchaFieldName(string $value): string
    {
        if (($client = $this->getClient()) === null) {
            throw new LogicException('Client not initialized.');
        }

        $crawler = $client->getCrawler()->filterXPath("//input[@type=\"checkbox\" and @value=\"$value\"]");
        if (1 !== $crawler->count()) {
            throw new LogicException("Expected to find a single '$value' element, found {$crawler->count()}.");
        }

        return $crawler->attr('name')
            ?? throw new LogicException("The '$value' element is missing the name attribute.");
    }

    protected static function assertCaptchaSolutionRejected(): void
    {
        self::assertSelectorExists('div.captcha.border-danger');
    }
}
