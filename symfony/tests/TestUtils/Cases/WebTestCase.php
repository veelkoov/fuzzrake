<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Tests\TestUtils\Cases\Traits\UtilsTrait;
use App\Utils\TestUtils\TestsBridge;
use LogicException;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;

abstract class WebTestCase extends SymfonyWebTestCase
{
    use UtilsTrait;

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        TestsBridge::reset();
    }

    protected function captchaRightSolutionFieldName(): string
    {
        if (($client = $this->getClient()) === null) {
            throw new LogicException('Client not initialized.');
        }

        $crawler = $client->getCrawler()->filterXPath('//input[@type="checkbox" and @value="right"]');
        if (1 !== $crawler->count()) {
            throw new LogicException('Expected to find a single "right" element, found '.$crawler->count());
        }

        return $crawler->attr('name')
            ?? throw new LogicException('The "right" element is missing the name attribute.');
    }

    protected static function assertCaptchaSolutionRejected(): void
    {
        self::assertSelectorExists('div.captcha.border-danger');
    }
}
