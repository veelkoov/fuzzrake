<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Utils\TestUtils\TestsBridge;
use Psl\Iter;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait IuFormTrait
{
    private static function skipRulesAndCaptcha(KernelBrowser $client): void
    {
        TestsBridge::setSkipSingleCaptcha();

        $client->submit($client->getCrawler()->selectButton('Agree and continue')->form());
        $client->followRedirect();
    }

    private static function skipData(KernelBrowser $client, bool $fillMandatoryData): void
    {
        $data = !$fillMandatoryData ? [] : [
            'iu_form[name]'            => 'Test name',
            'iu_form[country]'         => 'Test country',
            'iu_form[ages]'            => 'ADULTS',
            'iu_form[worksWithMinors]' => 'NO',
            'iu_form[nsfwWebsite]'     => 'NO',
            'iu_form[nsfwSocial]'      => 'NO',
            'iu_form[doesNsfw]'        => 'NO',
            'iu_form[makerId]'         => 'TESTMID',
        ];

        $form = $client->getCrawler()->selectButton('Continue')->form($data);

        self::submitValid($client, $form);
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

    private static function assertFieldErrorContactInfoMustNotBeBlank(): void
    {
        self::assertSelectorTextContains('#iu_form_contactInfoObfuscated + .help-text + .invalid-feedback', 'This value should not be blank.');
    }

    private static function assertFieldErrorPasswordIsRequired(): void
    {
        self::assertSelectorTextContains('#iu_form_password + .help-text + .invalid-feedback', 'Password is required.');
    }
}
