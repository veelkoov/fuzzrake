<?php

declare(strict_types=1);

namespace App\Tests\Controller\Traits;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use Override;

/**
 * @medium
 */
class ButtonClickedTraitTest extends FuzzrakeWebTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::$client->setServerParameters([
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'testing',
        ]);
    }

    public function testClicked(): void
    {
        $event = self::addSimpleGenericEvent();

        $editUrl = '/mx/events/'.$event->getId().'/edit';

        $invalidData = ['event[newCreatorsCount]' => '-1'];
        $invalidCsrf = ['event[_token]' => 'invalid'];
        $validData = [];

        /* Make sure CSRF tokens are being validated in the first place */
        self::$client->request('GET', $editUrl);
        self::submitInvalidForm(self::$client, 'Save', $invalidCsrf);

        /* Make sure SAVE button works for valid data */
        self::submitValidForm(self::$client, 'Save', $validData);

        /* Make sure validation works as intended for SAVE button, so we can test if DELETE ignores it */
        self::$client->request('GET', $editUrl);
        self::submitInvalidForm(self::$client, 'Save', $invalidData);

        /* Make sure DELETE doesn't work with wrong CSRF even if form is OK */
        self::$client->request('GET', $editUrl);
        self::submitInvalidForm(self::$client, 'Delete', $invalidCsrf);

        /* Make sure DELETE works with invalid data as long as CSRF is OK */
        self::submitValidForm(self::$client, 'Delete', $invalidData);
    }
}
