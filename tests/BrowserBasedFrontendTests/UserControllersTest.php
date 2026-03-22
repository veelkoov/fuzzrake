<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\TestUtils\Cases\FuzzrakePantherTestCase;
use Exception;
use PHPUnit\Framework\Attributes\Large;

#[Large]
class UserControllersTest extends FuzzrakePantherTestCase
{
    /**
     * @throws Exception
     */
    public function testContactAllowanceProsConsAreToggling(): void
    {
        self::haveACreatorUser();
        self::loginCreatorUser();

        self::$client->waitFor('body.init-done', 5);
        $form = self::$client->getCrawler()->selectButton('Save')->form();

        $noSelectionYet = '.pros-cons-contact-options[data-min-level="-1"][data-max-level="-1"]';
        $neverOnly = '.pros-cons-contact-options[data-min-level="0"][data-max-level="0"]';
        $feedbackOnly = '.pros-cons-contact-options[data-min-level="3"][data-max-level="3"]';
        $anythingButFeedback = '.pros-cons-contact-options[data-min-level="0"][data-max-level="2"]';

        self::$client->waitForVisibility($noSelectionYet, 5);
        self::$client->waitForInvisibility($neverOnly, 5);
        self::$client->waitForInvisibility($anythingButFeedback, 5);
        self::$client->waitForInvisibility($feedbackOnly, 5);

        $form->setValues([
            'contact_form[contactPermit]' => 'NO',
        ]);

        self::$client->waitForInvisibility($noSelectionYet, 5);
        self::$client->waitForVisibility($neverOnly, 5);
        self::$client->waitForVisibility($anythingButFeedback, 5);
        self::assertSelectorIsNotVisible($feedbackOnly);

        $form->setValues([
            'contact_form[contactPermit]' => 'FEEDBACK',
        ]);

        self::assertSelectorIsNotVisible($noSelectionYet);
        self::$client->waitForInvisibility($neverOnly, 5);
        self::$client->waitForInvisibility($anythingButFeedback, 5);
        self::$client->waitForVisibility($feedbackOnly, 5);
    }
}
