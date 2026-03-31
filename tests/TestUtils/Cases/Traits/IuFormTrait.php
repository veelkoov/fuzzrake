<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

trait IuFormTrait
{
    private static function skipRules(): void
    {
        self::$client->submit(self::$client->getCrawler()->selectButton('Agree and continue')->form());
        self::$client->followRedirect();
    }

    private static function assertIuSubmissionQueued(): void
    {
        self::assertSelectorTextContains('div.border-success .card-header', 'Your submission has been queued');
        self::assertSelectorTextContains('div.border-success p', 'Submissions are typically processed once a week, during the weekend. If you don\'t see your changes on-line after 7 days');
    }
}
