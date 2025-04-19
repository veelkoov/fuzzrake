<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use Psl\Iter;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait IuFormTrait
{
    private static function skipRules(KernelBrowser $client): void
    {
        $client->submit($client->getCrawler()->selectButton('Agree and continue')->form());
        $client->followRedirect();
    }

    private static function assertIuSubmittedAnyResult(KernelBrowser $client): void
    {
        $text = $client->getCrawler()->filter('.card-header')->text();

        self::assertTrue(Iter\any(
            ['Your submission has been queued', 'Submission recorded, but on hold'],
            fn (string $candidate) => str_contains($text, $candidate))
        );
    }

    private function assertIuSubmittedCorrectPassword(): void
    {
        self::assertSelectorTextContains('div.border-success .card-header', 'Your submission has been queued');
        self::assertSelectorTextContains('div.border-success p', 'Submissions are typically processed once a week, during the weekend. If you don\'t see your changes on-line after 7 days');
    }

    private function assertIuSubmittedWrongPasswordContactAllowed(): void
    {
        self::assertSelectorTextContains('div.border-warning .card-header', 'Your submission has been queued');
        self::assertSelectorTextContains('div.border-warning p', 'You requested a password change, so expect to be contacted by the maintainer to confirm your changes.');
    }

    private function assertIuSubmittedWrongPasswordContactNotAllowed(): void
    {
        self::assertSelectorTextContains('div.border-danger .card-header', 'Submission recorded, but on hold');
        self::assertSelectorTextContains('div.border-danger p', 'You requested a password change, but you didn\'t agree to be contacted, so');
    }

    private function assertIuSubmittedWrongPasswordContactWasNotAllowed(): void
    {
        self::assertSelectorTextContains('div.border-danger .card-header', 'Submission recorded, but on hold');
        self::assertSelectorTextContains('div.border-danger p', 'You requested a password change, but you didn\'t agree before to be contacted, so');
    }

    private static function assertFieldErrorValidEmailAddressRequired(): void
    {
        self::assertSelectorTextContains('#iu_form_emailAddress + .help-text + .invalid-feedback', 'A valid email address is required.');
    }

    private static function assertFieldErrorPasswordIsRequired(): void
    {
        self::assertSelectorTextContains('#iu_form_password + .help-text + .invalid-feedback', 'Password is required.');
    }
}
