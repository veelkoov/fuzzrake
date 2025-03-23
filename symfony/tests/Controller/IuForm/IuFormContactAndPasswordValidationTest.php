<?php

declare(strict_types=1);

namespace App\Tests\Controller\IuForm;

use App\Data\Definitions\ContactPermit;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @medium
 */
class IuFormContactAndPasswordValidationTest extends WebTestCaseWithEM
{
    use IuFormTrait;

    public function iuFormContactAndPasswordValidationDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            [
                null, // No pre-existing maker
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
                null, // No pre-existing maker
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
                null, // No pre-existing maker
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
        $iuFormStartUri = $itIsAnUpdate ? '/iu_form/start/MAKERID' : '/iu_form/start';

        $client = static::createClient();

        if ($itIsAnUpdate) {
            self::persistAndFlush(self::getArtisan(
                makerId: 'MAKERID',
                password: 'previous-password',
                contactAllowed: $previousContactPermit,
            ));
        }

        $client->request('GET', $iuFormStartUri);
        self::skipRulesAndCaptcha($client);

        $formData = [
            'iu_form[makerId]'         => 'MAKERID',
            'iu_form[name]'            => 'Test name',
            'iu_form[country]'         => 'Test country',
            'iu_form[ages]'            => 'MIXED',
            'iu_form[nsfwWebsite]'     => 'NO',
            'iu_form[nsfwSocial]'      => 'NO',
            'iu_form[worksWithMinors]' => 'NO',
        ];

        if (null !== $password) {
            $formData['iu_form[password]'] = $password;
        }

        if (null !== $contactPermit) {
            $formData['iu_form[contactAllowed]'] = $contactPermit;
        }

        if (null !== $email) {
            $formData['iu_form[emailAddressObfuscated]'] = $email;
        }

        if ($selectChangePassword) {
            $formData['iu_form[changePassword]'] = '1';
        }

        if ($selectVerificationAck) {
            $formData['iu_form[verificationAcknowledgement]'] = '1';
        }

        $form = $client->getCrawler()->selectButton('Submit')->form($formData);

        if ($shouldSucceed) {
            self::submitValid($client, $form);
        } else {
            self::submitInvalid($client, $form);
        }

        $assertions();
    }
}
