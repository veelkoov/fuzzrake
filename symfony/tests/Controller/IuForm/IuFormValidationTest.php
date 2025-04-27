<?php

declare(strict_types=1);

namespace App\Tests\Controller\IuForm;

use App\Data\Definitions\ContactPermit;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @medium
 */
class IuFormValidationTest extends FuzzrakeWebTestCase
{
    use IuFormTrait;

    public function testErrorMessagesForRequiredDataFields(): void
    {
        self::$client->request('GET', '/iu_form/start');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form();
        self::submitInvalid($form);

        self::assertCaptchaSolutionRejected();

        self::assertSelectorTextContains('#iu_form_name + .invalid-feedback',
            'This value should not be blank.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(1)',
            'Studio/maker\'s name - This value should not be blank.');
        self::assertSelectorTextContains('#iu_form_country + .invalid-feedback',
            'This value should not be blank.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(2)',
            'Country - This value should not be blank.');
        self::assertSelectorTextContains('#iu_form_ages + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(3)',
            'What is your age? - You must answer this question.');
        self::assertSelectorTextContains('#iu_form_nsfwWebsite + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(4)',
            'The websites linked above may contain "non-family-friendly" (or NSFW) content, such as, but not limited to: - You must answer this question.');
        self::assertSelectorTextContains('#iu_form_nsfwSocial + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(5)',
            'Is there a possibility of NSFW (or the type of content listed above) being liked/shared/posted/commented on by your social media account? - You must answer this question.');
        self::assertSelectorTextContains('#iu_form_creatorId + .help-text + .invalid-feedback',
            'This value should not be blank.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(6)',
            '"Maker ID" - This value should not be blank.');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[ages]'        => 'MINORS',
            'iu_form[nsfwWebsite]' => 'NO',
            'iu_form[nsfwSocial]'  => 'NO',
        ]);
        self::submitInvalid($form);

        self::assertSelectorTextContains('#iu_form_worksWithMinors + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(3)',
            'Do you accept commissions from minors or people under 18? - You must answer this question.');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[ages]'        => 'ADULTS',
            'iu_form[nsfwWebsite]' => 'NO',
            'iu_form[nsfwSocial]'  => 'NO',
        ]);
        self::submitInvalid($form);

        self::assertSelectorTextContains('#iu_form_doesNsfw + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(3)',
            'Do you offer fursuit features intended for adult use?');
    }

    /**
     * @param array<string, string> $expectedErrors
     *
     * @dataProvider ageStuffFieldsDataProvider
     */
    public function testAgeStuffFields(string $ages, string $nsfwWebsite, string $nsfwSocial, ?string $doesNsfw, ?string $worksWithMinors, array $expectedErrors): void
    {
        self::$client->request('GET', '/iu_form/start');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]' => 'test-maker-555',
            'iu_form[country]' => 'Finland',
            'iu_form[creatorId]' => 'TEST001',
            'iu_form[ages]' => $ages,
            'iu_form[nsfwWebsite]' => $nsfwWebsite,
            'iu_form[nsfwSocial]' => $nsfwSocial,
            'iu_form[contactAllowed]' => 'NO',
            'iu_form[password]' => 'aBcDeFgH1324',
            $this->getCaptchaFieldName('right') => 'right',
        ]);

        if (null !== $doesNsfw) {
            $form->setValues(['iu_form[doesNsfw]' => $doesNsfw]);
        }

        if (null !== $worksWithMinors) {
            $form->setValues(['iu_form[worksWithMinors]' => $worksWithMinors]);
        }

        if ([] === $expectedErrors) {
            self::submitValid($form);
        } else {
            self::submitInvalid($form);

            foreach ($expectedErrors as $selector => $message) {
                self::assertSelectorTextContains($selector, $message);
            }
        }
    }

    public function ageStuffFieldsDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            // AGES    NSFW   NSFW    DOES   WORKS     EXPECTED
            //         WEB.   SOCIAL  NSFW   W/MINORS  ERRORS
            ['MINORS', 'NO',  'NO',   null,  null,     [
                '#iu_form_worksWithMinors + .invalid-feedback' => 'You must answer this question.',
            ]],
            ['MINORS', 'NO',  'NO',   null,  'NO',     []],
            ['MINORS', 'NO',  'YES',  null,  null,     []],
            ['MINORS', 'YES', 'NO',   null,  null,     []],
            ['MINORS', 'YES', 'YES',  null,  null,     []],

            ['MIXED',  'NO',  'NO',   null,  null,     [
                '#iu_form_worksWithMinors + .invalid-feedback' => 'You must answer this question.',
            ]],
            ['MIXED',  'NO',  'NO',   null,  'NO',     []],
            ['MIXED',  'NO',  'YES',  null,  null,     []],
            ['MIXED',  'YES', 'NO',   null,  null,     []],
            ['MIXED',  'YES', 'YES',  null,  null,     []],

            ['ADULTS', 'NO',  'NO',   null,  null,     [
                '#iu_form_doesNsfw + .invalid-feedback'        => 'You must answer this question.',
            ]],
            ['ADULTS', 'NO',  'NO',   'NO',  null,     [
                '#iu_form_worksWithMinors + .invalid-feedback' => 'You must answer this question.',
            ]],
            ['ADULTS', 'NO',  'NO',   'NO',  'NO',     []],
            ['ADULTS', 'NO',  'NO',   'NO',  'YES',    []],
            ['ADULTS', 'NO',  'NO',   'YES', null,     []],

            ['ADULTS', 'NO',  'YES',  null,  null,     [
                '#iu_form_doesNsfw + .invalid-feedback'        => 'You must answer this question.',
            ]],
            ['ADULTS', 'NO',  'YES',  'NO',  null,     []],
            ['ADULTS', 'NO',  'YES',  'YES', null,     []],

            ['ADULTS', 'YES', 'NO',   null,  null,     [
                '#iu_form_doesNsfw + .invalid-feedback'        => 'You must answer this question.',
            ]],
            ['ADULTS', 'YES', 'NO',   'NO',  null,     []],
            ['ADULTS', 'YES', 'NO',   'YES', null,     []],

            ['ADULTS', 'YES', 'YES',  null,  null,     [
                '#iu_form_doesNsfw + .invalid-feedback'        => 'You must answer this question.',
            ]],
            ['ADULTS', 'YES', 'YES',  'NO',  null,     []],
            ['ADULTS', 'YES', 'YES',  'NO',  null,     []],
            ['ADULTS', 'YES', 'YES',  'YES', null,     []],
            ['ADULTS', 'YES', 'YES',  'YES', null,     []],
        );
    }

    public function iuFormContactAndPasswordValidationDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            [
                null, // No pre-existing creator
                'new-password', // Setting a new password
                'FEEDBACK', // Contact allowed
                null, // ERROR: Not filling email address
                false, // Inaccessible option (password change request)
                false, // Inaccessible option (confirmation acknowledgement)
                false, // Failure expected
                function () {
                    self::assertFieldErrorValidEmailAddressRequired();
                },
            ],
            [
                null, // No pre-existing creator
                null, // ERROR: Not filling password
                'NO', // Not allowing contact
                null, // Email not required
                false, // Inaccessible option (password change request)
                false, // Inaccessible option (confirmation acknowledgement)
                false, // Failure expected
                function () {
                    self::assertFieldErrorPasswordIsRequired();
                },
            ],
            [
                null, // No pre-existing creator
                'new-password', // Setting a new password
                'NO', // Not allowing contact
                null, // Email not required
                false, // Inaccessible option (password change request)
                false, // Inaccessible option (confirmation acknowledgement)
                true, // Success expected
                function () {
                    self::assertIuSubmittedCorrectPassword();
                },
            ],
            [
                ContactPermit::NO, // Contact was not allowed
                'previous-password', // Providing correct password
                null, // Not changing contact permit
                null, // Email not required
                false, // No need to change password
                false, // Inaccessible option (confirmation acknowledgement)
                true, // Success expected
                function () {
                    self::assertIuSubmittedCorrectPassword();
                },
            ],
            [
                ContactPermit::CORRECTIONS, // Contact is allowed
                'new-password', // Providing a new password
                null, // Not changing contact permit
                'address@example.com',
                false, // ERROR: Not requesting a password change
                false, // Inaccessible option (confirmation acknowledgement)
                false, // Failure expected
                function () {
                    self::assertSelectorTextContains('div.invalid-feedback',
                        'Wrong password. To change your password, please select the "I want to change my password / I forgot my password" checkbox.');
                },
            ],
            [
                ContactPermit::NO, // Contact is allowed
                'new-password', // Providing a new password
                null, // Not changing contact permit
                'address@example.com',
                true, // Request password change
                false, // ERROR: Not acknowledging confirmation necessity
                false, // Failure expected
                function () {
                    self::assertSelectorTextContains('#verification_acknowledgement div.invalid-feedback',
                        'Your action is required; your submission will be rejected otherwise.');
                },
            ],
            [
                ContactPermit::CORRECTIONS, // Contact is allowed
                'new-password', // Providing a new password
                null, // Not changing contact permit
                'address@example.com',
                true, // Request password change
                true, // Acknowledge necessity to confirm
                true, // Success expected
                function () {
                    self::assertIuSubmittedWrongPasswordContactAllowed();
                },
            ],
            [
                ContactPermit::ANNOUNCEMENTS, // Contact was allowed
                'new-password', // Providing a new password
                'NO', // Contact is no longer allowed
                null, // Email not required
                true, // Request password change
                true, // Acknowledge confirmation necessity
                true, // Success expected
                function () {
                    self::assertIuSubmittedWrongPasswordContactNotAllowed();
                },
            ],
            [
                ContactPermit::NO, // Contact was not allowed
                'new-password', // Providing a new password
                'CORRECTIONS', // Contact is allowed now
                'address@example.com',
                true, // Request password change
                true, // Acknowledge confirmation necessity
                true, // Success expected
                function () {
                    self::assertIuSubmittedWrongPasswordContactWasNotAllowed();
                },
            ],
        );
    }

    /**
     * @dataProvider iuFormContactAndPasswordValidationDataProvider
     */
    public function testIuFormContactAndPasswordValidation(
        ?ContactPermit $previousContactPermit,
        ?string $password,
        ?string $contactPermit,
        ?string $email,
        bool $selectChangePassword,
        bool $selectVerificationAck,
        bool $shouldSucceed,
        callable $assertions,
    ): void {
        $itIsAnUpdate = null !== $previousContactPermit;
        $iuFormStartUri = $itIsAnUpdate ? '/iu_form/start/TEST001' : '/iu_form/start';

        if ($itIsAnUpdate) {
            self::persistAndFlush(self::getCreator(
                creatorId: 'TEST001',
                password: 'previous-password',
                contactAllowed: $previousContactPermit,
            ));
        }

        self::$client->request('GET', $iuFormStartUri);
        self::skipRules();

        $formData = [
            'iu_form[creatorId]'       => 'TEST001',
            'iu_form[name]'            => 'Test name',
            'iu_form[country]'         => 'Test country',
            'iu_form[ages]'            => 'MIXED',
            'iu_form[nsfwWebsite]'     => 'NO',
            'iu_form[nsfwSocial]'      => 'NO',
            'iu_form[worksWithMinors]' => 'NO',
            $this->getCaptchaFieldName('right') => 'right',
        ];

        if (null !== $password) {
            $formData['iu_form[password]'] = $password;
        }

        if (null !== $contactPermit) {
            $formData['iu_form[contactAllowed]'] = $contactPermit;
        }

        if (null !== $email) {
            $formData['iu_form[emailAddress]'] = $email;
        }

        if ($selectChangePassword) {
            $formData['iu_form[changePassword]'] = '1';
        }

        if ($selectVerificationAck) {
            $formData['iu_form[verificationAcknowledgement]'] = '1';
        }

        $form = self::$client->getCrawler()->selectButton('Submit')->form($formData);

        if ($shouldSucceed) {
            self::submitValid($form);
        } else {
            self::submitInvalid($form);
        }

        $assertions();
    }
}
