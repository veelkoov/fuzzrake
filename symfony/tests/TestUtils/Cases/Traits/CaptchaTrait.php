<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use LogicException;

trait CaptchaTrait
{
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
