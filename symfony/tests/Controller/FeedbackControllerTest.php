<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @medium
 */
class FeedbackControllerTest extends FuzzrakeWebTestCase
{
    public function testSimpleFeedbackSubmission(): void
    {
        self::$client->request('GET', '/feedback');

        self::assertResponseStatusCodeIs(200);

        $form = self::$client->getCrawler()->selectButton('Send')->form([
            'feedback[details]'       => 'Testing details',
            'feedback[creator]'       => 'TEST001',
            'feedback[subject]'       => 'Other (please provide adequate details and context)',
            'feedback[noContactBack]' => true,
            $this->getCaptchaFieldName('right') => 'right',
        ]);

        self::$client->submit($form);
        self::assertResponseStatusCodeIs(302);
        self::$client->followRedirect();

        self::assertSelectorTextSame('h1', 'Feedback submitted');
        self::assertSelectorTextContains('div.alert', 'Feedback has been successfully submitted.');
    }

    public function testCaptchaWorksBySimpleSubmission(): void
    {
        self::$client->request('GET', '/feedback');

        self::assertResponseStatusCodeIs(200);

        $form = self::$client->getCrawler()->selectButton('Send')->form([
            'feedback[details]'       => 'Testing details',
            'feedback[creator]'       => 'TEST001',
            'feedback[subject]'       => 'Other (please provide adequate details and context)',
            'feedback[noContactBack]' => true,
            $this->getCaptchaFieldName('wrong') => 'wrong',
        ]);
        self::submitInvalid(self::$client, $form);
        self::assertCaptchaSolutionRejected();

        $form = self::$client->getCrawler()->selectButton('Send')->form([
            'feedback[details]' => '', // To cause 422 and see if the captcha does not show again
            $this->getCaptchaFieldName('right') => 'right',
        ]);
        self::submitInvalid(self::$client, $form);

        $form = self::$client->getCrawler()->selectButton('Send')->form([
            'feedback[details]' => 'Testing details',
            // Captcha solved previously, not needed again
        ]);
        self::submitValid(self::$client, $form);

        self::assertSelectorTextSame('h1', 'Feedback submitted');
        self::assertSelectorTextContains('div.alert', 'Feedback has been successfully submitted.');
    }

    public function testSimpleValidationErrors(): void
    {
        self::$client->request('GET', '/feedback');

        self::assertResponseStatusCodeIs(200);

        $form = self::$client->getCrawler()->selectButton('Send')->form();
        self::$client->submit($form);

        self::assertResponseStatusCodeIs(422);
        self::assertSelectorTextSame('#feedback_subject + .invalid-feedback', 'This is required.');
        self::assertSelectorTextSame('#feedback_noContactBack_help + .invalid-feedback', 'This is required.');
        self::assertSelectorTextSame('#feedback_details + .invalid-feedback', 'This is required.');
    }

    /**
     * @dataProvider blockedOptionsDataProvider
     */
    public function testBlockedOptions(string $optionToSelect, bool $shouldBlock): void
    {
        self::$client->request('GET', '/feedback');

        self::assertResponseStatusCodeIs(200);

        $form = self::$client->getCrawler()->selectButton('Send')->form([
            'feedback[details]'       => 'Testing details',
            'feedback[creator]'       => 'TEST001',
            'feedback[subject]'       => $optionToSelect,
            'feedback[noContactBack]' => true,
            $this->getCaptchaFieldName('right') => 'right',
        ]);

        self::$client->submit($form);

        self::assertResponseStatusCodeIs($shouldBlock ? 422 : 302);
    }

    public function blockedOptionsDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            ['Help me get a fursuit', true],
            ["Maker's commissions info (open/closed) is inaccurate", true],
            ["Maker's website/social account is no longer working", false],
            ["Other maker's information is (partially) outdated", true],
            ['Other information on this website needs attention (not related to a particular maker)', false],
            ['Report a technical problem/bug with this website', false],
            ['Suggest an improvement to this website', false],
            ['Other (please provide adequate details and context)', false],
        );
    }
}
