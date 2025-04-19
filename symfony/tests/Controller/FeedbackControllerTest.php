<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\WebTestCase;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @medium
 */
class FeedbackControllerTest extends WebTestCase
{
    public function testSimpleFeedbackSubmission(): void
    {
        $client = $this->createClient();
        $client->request('GET', '/feedback');

        self::assertResponseStatusCodeIs($client, 200);

        $form = $client->getCrawler()->selectButton('Send')->form([
            'feedback[details]'       => 'Testing details',
            'feedback[maker]'         => 'MAKERID',
            'feedback[subject]'       => 'Other (please provide adequate details and context)',
            'feedback[noContactBack]' => true,
            $this->captchaRightSolutionFieldName() => true,
        ]);

        $client->submit($form);
        self::assertResponseStatusCodeIs($client, 302);
        $client->followRedirect();

        self::assertSelectorTextSame('h1', 'Feedback submitted');
        self::assertSelectorTextContains('div.alert', 'Feedback has been successfully submitted.');
    }

    public function testCaptchaFailure(): void
    {
        $client = $this->createClient();
        $client->request('GET', '/feedback');

        self::assertResponseStatusCodeIs($client, 200);

        $form = $client->getCrawler()->selectButton('Send')->form([
            'feedback[details]'       => 'Testing details',
            'feedback[maker]'         => 'MAKERID',
            'feedback[subject]'       => 'Other (please provide adequate details and context)',
            'feedback[noContactBack]' => true,
        ]);

        $client->submit($form);
        self::assertResponseStatusCodeIs($client, 422);
        self::assertSelectorTextSame('h1', 'Feedback form');
        self::assertCaptchaSolutionRejected();
    }

    public function testSimpleValidationErrors(): void
    {
        $client = $this->createClient();
        $client->request('GET', '/feedback');

        self::assertResponseStatusCodeIs($client, 200);

        $form = $client->getCrawler()->selectButton('Send')->form();
        $client->submit($form);

        self::assertResponseStatusCodeIs($client, 422);
        self::assertSelectorTextSame('#feedback_subject + .invalid-feedback', 'This is required.');
        self::assertSelectorTextSame('#feedback_noContactBack_help + .invalid-feedback', 'This is required.');
        self::assertSelectorTextSame('#feedback_details + .invalid-feedback', 'This is required.');
    }

    /**
     * @dataProvider blockedOptionsDataProvider
     */
    public function testBlockedOptions(string $optionToSelect, bool $shouldBlock): void
    {
        $client = $this->createClient();
        $client->request('GET', '/feedback');

        self::assertResponseStatusCodeIs($client, 200);

        $form = $client->getCrawler()->selectButton('Send')->form([
            'feedback[details]'       => 'Testing details',
            'feedback[maker]'         => 'MAKERID',
            'feedback[subject]'       => $optionToSelect,
            'feedback[noContactBack]' => true,
            self::captchaRightSolutionFieldName() => 'right',
        ]);

        $client->submit($form);

        self::assertResponseStatusCodeIs($client, $shouldBlock ? 422 : 302);
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
