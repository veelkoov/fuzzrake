<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Tests\TestUtils\Cases\FuzzrakePantherTestCase;
use Exception;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Large;
use TRegx\PhpUnit\DataProviders\DataProvider as TestDataProvider;

#[Large]
class IuFormTest extends FuzzrakePantherTestCase
{
    public static function passwordCheckBoxesDataProvider(): TestDataProvider
    {
        return TestDataProvider::tuples(
            [null,                    ContactPermit::FEEDBACK, false, false],
            [ContactPermit::FEEDBACK, ContactPermit::FEEDBACK, true,  false],
            [ContactPermit::NO,       ContactPermit::FEEDBACK, true,  true],
            [ContactPermit::FEEDBACK, ContactPermit::NO,       true,  true],
        );
    }

    /**
     * @throws WebDriverException
     */
    #[DataProvider('passwordCheckBoxesDataProvider')]
    public function testPasswordCheckboxes(?ContactPermit $previousContactPermitIfUpdate, ContactPermit $contactPermit,
        bool $passwordChangePossible, bool $confirmationAcknowledgementAvailable): void
    {
        $isUpdate = null !== $previousContactPermitIfUpdate;

        $this->setupIuTestGoToTheDataPage($previousContactPermitIfUpdate);
        self::waitUntilHides($isUpdate ? '#forgotten_password_instructions' : '#email-address');

        self::$client->getCrawler()->selectButton('Submit')->form()->setValues([
            'iu_form[contactAllowed]' => $contactPermit->value,
        ]);

        if ($passwordChangePossible) {
            self::$client->findElement(WebDriverBy::id('iu_form_changePassword'))->click();
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
    public function testNewCreatorEmailVisibilityAndRequirement(): void
    {
        $this->goToTheDataPage();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactAllowed]' => 'FEEDBACK',
        ]);

        self::waitUntilShows('#iu_form_emailAddress');
        self::assertSelectorExists('#iu_form_emailAddress[required]');

        $form->setValues([
            'iu_form[contactAllowed]' => 'NO',
        ]);

        self::waitUntilHides('#iu_form_emailAddress');
        self::assertSelectorExists('#iu_form_emailAddress:not([required])');
    }

    /**
     * @throws Exception
     */
    public function testExistingCreatorPreviouslyNotAllowedEmailRequirement(): void
    {
        self::persistAndFlush(self::getCreator(creatorId: 'TEST001', contactAllowed: ContactPermit::NO));
        $this->goToTheDataPage('TEST001');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactAllowed]' => 'FEEDBACK',
        ]);

        self::waitUntilShows('#iu_form_emailAddress');
        self::assertSelectorExists('#iu_form_emailAddress[required]');

        $form->setValues([
            'iu_form[contactAllowed]' => 'NO',
        ]);

        self::waitUntilHides('#iu_form_emailAddress');
        self::assertSelectorExists('#iu_form_emailAddress:not([required])');
    }

    /**
     * @throws Exception
     */
    public function testExistingCreatorWithAnEmailEmailRequirement(): void
    {
        self::persistAndFlush(self::getCreator(creatorId: 'TEST001', contactAllowed: ContactPermit::NO, emailAddress: 'example@example.com'));
        $this->goToTheDataPage('TEST001');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[contactAllowed]' => 'FEEDBACK',
        ]);

        self::waitUntilShows('#iu_form_emailAddress');
        self::assertSelectorExists('#iu_form_emailAddress:not([required])');

        $form->setValues([
            'iu_form[contactAllowed]' => 'NO',
        ]);

        self::waitUntilHides('#iu_form_emailAddress');
        self::assertSelectorExists('#iu_form_emailAddress:not([required])');
    }

    /**
     * @throws Exception
     */
    public function testContactAllowanceProsConsAreToggling(): void
    {
        $this->setupIuTestGoToTheDataPage();

        $form = self::$client->getCrawler()->selectButton('Submit')->form();

        $noSelectionYet = '.pros-cons-contact-options[data-min-level="-1"][data-max-level="-1"]';
        $neverOnly = '.pros-cons-contact-options[data-min-level="0"][data-max-level="0"]';
        $feedbackOnly = '.pros-cons-contact-options[data-min-level="3"][data-max-level="3"]';
        $anythingButFeedback = '.pros-cons-contact-options[data-min-level="0"][data-max-level="2"]';

        self::$client->waitForVisibility($noSelectionYet, 5);
        self::$client->waitForInvisibility($neverOnly, 5);
        self::$client->waitForInvisibility($anythingButFeedback, 5);
        self::$client->waitForInvisibility($feedbackOnly, 5);

        $form->setValues([
            'iu_form[contactAllowed]' => 'NO',
        ]);

        self::$client->waitForInvisibility($noSelectionYet, 5);
        self::$client->waitForVisibility($neverOnly, 5);
        self::$client->waitForVisibility($anythingButFeedback, 5);
        self::assertSelectorIsNotVisible($feedbackOnly);

        $form->setValues([
            'iu_form[contactAllowed]' => 'FEEDBACK',
        ]);

        self::assertSelectorIsNotVisible($noSelectionYet);
        self::$client->waitForInvisibility($neverOnly, 5);
        self::$client->waitForInvisibility($anythingButFeedback, 5);
        self::$client->waitForVisibility($feedbackOnly, 5);
    }

    /**
     * @throws WebDriverException
     */
    private function setupIuTestGoToTheDataPage(?ContactPermit $previousContactPermitIfUpdate = null): void
    {
        $isUpdate = null !== $previousContactPermitIfUpdate;

        if ($isUpdate) {
            self::persistAndFlush(self::getCreator(creatorId: 'TEST001', contactAllowed: $previousContactPermitIfUpdate));
        }

        $this->goToTheDataPage($isUpdate ? 'TEST001' : null);
    }

    /**
     * Assure that:
     * - I/U form state is NOT shared between new creator and existing different creators.
     * - I/U form state is kept until the form gets reset or submitted.
     *
     * @throws Exception
     */
    public function testFormStateIsProperlyKeptAndReset(): void
    {
        // Having two existing creators
        self::persistAndFlush(
            self::getCreator(name: 'Creator 001', creatorId: 'TEST001', password: 'test-password', contactAllowed: ContactPermit::NO, ages: Ages::MIXED, nsfwWebsite: false, nsfwSocial: false, doesNsfw: false, worksWithMinors: false),
            self::getCreator(name: 'Creator 002', creatorId: 'TEST002', password: 'test-password', contactAllowed: ContactPermit::NO, ages: Ages::MIXED, nsfwWebsite: false, nsfwSocial: false, doesNsfw: false, worksWithMinors: false),
        );

        // Load 1st creator I/U data page, change some stuff A
        $this->goToTheDataPage('TEST001');
        self::assertInputValueSame('iu_form[name]', 'Creator 001');
        self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]' => 'Creator 001 - MODIFIED',
        ]);
        self::$client->getKeyboard()->pressKey(WebDriverKeys::TAB); // Simulate exiting field's focus

        // Load new creator I/U data page, set some stuff B
        $this->goToTheDataPage();
        self::assertInputValueSame('iu_form[name]', '');
        self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]' => 'New creator - MODIFIED',
            'iu_form[creatorId]' => 'TEST003',
            'iu_form[country]' => 'FI',
            'iu_form[ages]' => 'MIXED',
            'iu_form[nsfwWebsite]' => 'NO',
            'iu_form[nsfwSocial]' => 'YES',
            'iu_form[contactAllowed]' => 'NO',
        ]);
        self::$client->getKeyboard()->pressKey(WebDriverKeys::TAB); // Simulate exiting field's focus

        // Load 2nd creator I/U data page, change some stuff C
        $this->goToTheDataPage('TEST002');
        self::assertInputValueSame('iu_form[name]', 'Creator 002');
        self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]' => 'Creator 002 - MODIFIED',
        ]);
        self::$client->getKeyboard()->pressKey(WebDriverKeys::TAB); // Simulate exiting field's focus

        // Go back to 1st creator I/U data page, make sure A matches, submit
        $this->goToTheDataPage('TEST001');
        self::assertInputValueSame('iu_form[name]', 'Creator 001 - MODIFIED');
        $this->selectRightCaptchaSolution();
        self::$client->submit(self::$client->getCrawler()->selectButton('Submit')->form(), [
            'iu_form[password]' => 'test-password',
        ]);
        self::$client->waitFor('#iu-form-data[data-step="confirmation"]');

        // Go back to the new creator I/U data page, make sure B matches, reset
        $this->goToTheDataPage();
        self::assertInputValueSame('iu_form[name]', 'New creator - MODIFIED');
        self::$client->findElement(WebDriverBy::id('iu-form-reset-button'))->click();
        self::$client->getWebDriver()->switchTo()->alert()->accept();
        self::$client->waitFor('#iu-form-data[data-step="data"]');

        // Go back to the 1st creator I/U data page, make sure it's clean
        $this->goToTheDataPage('TEST001');
        self::assertInputValueSame('iu_form[name]', 'Creator 001');

        // Go back to the new creator I/U data page, make sure it's clean
        $this->goToTheDataPage();
        self::assertInputValueSame('iu_form[name]', '');

        // Go back to the 2nd creator I/U data page, make sure C matches
        $this->goToTheDataPage('TEST002');
        self::assertInputValueSame('iu_form[name]', 'Creator 002 - MODIFIED');
    }

    /**
     * @throws WebDriverException
     */
    private function goToTheDataPage(?string $creatorId = null): void
    {
        $isUpdate = null !== $creatorId;

        $iuFormStartUri = '/index.php/iu_form/start'.($isUpdate ? "/$creatorId" : '');
        self::$client->request('GET', $iuFormStartUri);

        $waitThenClick = $isUpdate ? [
            '#iu_form_confirmUpdatingTheRightOne_0',
            '#iu_form_confirmYouAreTheCreator_0',
            '#iu_form_confirmNoPendingUpdates_0',
        ] : [
            '#iu_form_confirmAddingANewOne_0',
            '#iu_form_ensureStudioIsNotThereAlready_0',
            '#iu_form_confirmYouAreTheCreator_0',
            '#iu_form_confirmNoPendingUpdates_0',
        ];

        foreach ($waitThenClick as $cssSelector) {
            self::waitUntilShows($cssSelector);
            self::$client->findElement(WebDriverBy::cssSelector($cssSelector))->click();
        }

        self::waitUntilShows('#rulesAndContinueButton');
        self::$client->findElement(WebDriverBy::cssSelector('input[type=submit]'))->click();
        self::$client->waitForVisibility('#iu_form_emailAddress', 5);
    }
}
