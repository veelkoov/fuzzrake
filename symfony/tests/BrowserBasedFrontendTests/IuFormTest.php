<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Data\Definitions\ContactPermit;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use Exception;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @large
 */
class IuFormTest extends PantherTestCaseWithEM
{
    public function passwordCheckBoxesDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            [null,                    ContactPermit::FEEDBACK, false, false],
            [ContactPermit::FEEDBACK, ContactPermit::FEEDBACK, true,  false],
            [ContactPermit::NO,       ContactPermit::FEEDBACK, true,  true],
            [ContactPermit::FEEDBACK, ContactPermit::NO,       true,  true],
        );
    }

    /**
     * @dataProvider passwordCheckBoxesDataProvider
     *
     * @throws WebDriverException
     */
    public function testPasswordCheckboxes(?ContactPermit $previousContactPermitIfUpdate, ContactPermit $contactPermit,
        bool $passwordChangePossible, bool $confirmationAcknowledgementAvailable): void
    {
        $isUpdate = null !== $previousContactPermitIfUpdate;

        $this->setupIuTestGoToTheLastPage($previousContactPermitIfUpdate);
        self::waitUntilHides($isUpdate ? '#forgotten_password_instructions' : '#contact_info');

        $this->client->getCrawler()->selectButton('Submit')->form()->setValues([
            'iu_form[contactAllowed]' => $contactPermit->value,
        ]);

        if ($passwordChangePossible) {
            $this->client->findElement(WebDriverBy::id('iu_form_changePassword'))->click();
            self::waitUntilShows('#forgotten_password_instructions');
        } else {
            self::waitUntilHides('#iu_form_changePassword');
        }

        if ($confirmationAcknowledgementAvailable) {
            self::waitUntilShows('#iu_form_verificationAcknowledgement');
        } else {
            self::waitUntilHides('#iu_form_verificationAcknowledgement');
        }
    }

    /**
     * @throws Exception
     */
    public function testContactMethodNotRequiredAndHiddenWhenContactNotAllowed(): void
    {
        $this->setupIuTestGoToTheLastPage();

        $form = $this->client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactAllowed]' => 'FEEDBACK',
        ]);
        $this->client->waitForVisibility('#iu_form_contactInfoObfuscated', 5);

        $this->client->waitForVisibility('#iu_form_contactInfoObfuscated', 5);
        self::assertSelectorIsVisible('#iu_form_contactInfoObfuscated');
        self::assertSelectorExists('#iu_form_contactInfoObfuscated[required]');

        $form->setValues([
            'iu_form[contactAllowed]' => 'NO',
        ]);
        $this->client->waitForInvisibility('#iu_form_contactInfoObfuscated', 5);

        $this->client->waitForInvisibility('#iu_form_contactInfoObfuscated', 5);
        self::assertSelectorIsNotVisible('#iu_form_contactInfoObfuscated');
        self::assertSelectorExists('#iu_form_contactInfoObfuscated:not([required])');
    }

    /**
     * @throws Exception
     */
    public function testContactAllowanceProsConsAreToggling(): void
    {
        $this->setupIuTestGoToTheLastPage();

        $form = $this->client->getCrawler()->selectButton('Submit')->form();

        $noSelectionYet = '.pros-cons-contact-options[data-min-level="-1"][data-max-level="-1"]';
        $neverOnly = '.pros-cons-contact-options[data-min-level="0"][data-max-level="0"]';
        $feedbackOnly = '.pros-cons-contact-options[data-min-level="3"][data-max-level="3"]';
        $anythingButFeedback = '.pros-cons-contact-options[data-min-level="0"][data-max-level="2"]';

        $this->client->waitForVisibility($noSelectionYet, 5);
        $this->client->waitForInvisibility($neverOnly, 5);
        $this->client->waitForInvisibility($anythingButFeedback, 5);
        $this->client->waitForInvisibility($feedbackOnly, 5);

        $form->setValues([
            'iu_form[contactAllowed]' => 'NO',
        ]);

        $this->client->waitForInvisibility($noSelectionYet, 5);
        $this->client->waitForVisibility($neverOnly, 5);
        $this->client->waitForVisibility($anythingButFeedback, 5);
        self::assertSelectorIsNotVisible($feedbackOnly);

        $form->setValues([
            'iu_form[contactAllowed]' => 'FEEDBACK',
        ]);

        self::assertSelectorIsNotVisible($noSelectionYet);
        $this->client->waitForInvisibility($neverOnly, 5);
        $this->client->waitForInvisibility($anythingButFeedback, 5);
        $this->client->waitForVisibility($feedbackOnly, 5);
    }

    /**
     * @throws WebDriverException
     */
    private function setupIuTestGoToTheLastPage(?ContactPermit $previousContactPermitIfUpdate = null): void
    {
        $isUpdate = null !== $previousContactPermitIfUpdate;

        if ($isUpdate) {
            self::persistAndFlush(self::getArtisan(makerId: 'MAKERID', contactAllowed: $previousContactPermitIfUpdate));
        }

        $iuFormStartUri = $isUpdate ? '/index.php/iu_form/start/MAKERID' : '/index.php/iu_form/start';
        $this->client->request('GET', $iuFormStartUri);

        $waitThenClick = $isUpdate ? [
            '#iu_form_confirmUpdatingTheRightOne_0',
            '#iu_form_confirmYouAreTheMaker_0',
            '#iu_form_confirmNoPendingUpdates_0',
        ] : [
            '#iu_form_confirmAddingANewOne_0',
            '#iu_form_ensureStudioIsNotThereAlready_0',
            '#iu_form_confirmYouAreTheMaker_0',
            '#iu_form_confirmNoPendingUpdates_0',
        ];

        foreach ($waitThenClick as $cssSelector) {
            self::waitUntilShows($cssSelector);
            $this->client->findElement(WebDriverBy::cssSelector($cssSelector))->click();
        }

        self::waitUntilShows('#rulesAndContinueButton');
        $this->client->findElement(WebDriverBy::cssSelector('input[type=submit]'))->click();

        $this->client->waitForVisibility('#iu_form_name', 5);
        $this->client->submitForm('Continue', [
            'iu_form[name]'            => 'Testing',
            'iu_form[makerId]'         => 'MAKERID',
            'iu_form[country]'         => 'FI',
            'iu_form[ages]'            => 'MIXED',
            'iu_form[nsfwWebsite]'     => 'YES',
            'iu_form[nsfwSocial]'      => 'NO',
        ]);

        $this->client->waitForVisibility('#iu_form_contactInfoObfuscated', 5);
    }
}
