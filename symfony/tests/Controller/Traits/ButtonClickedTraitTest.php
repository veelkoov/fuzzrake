<?php

declare(strict_types=1);

namespace App\Tests\Controller\Traits;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;

/**
 * @medium
 */
class ButtonClickedTraitTest extends WebTestCaseWithEM
{
    public function testClicked(): void
    {
        $client = self::createClient();
        $event = self::addSimpleGenericEvent();

        $editUrl = '/mx/events/'.$event->getId().'/edit';

        $invalidData = ['event[newMakersCount]' => '-1'];
        $invalidCsrf = ['event[_token]' => 'invalid'];
        $validData = [];

        /* Make sure CSRF tokens are being validated in the first place */
        $client->request('GET', $editUrl);
        self::submitInvalidForm($client, 'Save', $invalidCsrf);

        /* Make sure SAVE button works for valid data */
        self::submitValidForm($client, 'Save', $validData);

        /* Make sure validation works as intended for SAVE button, so we can test if DELETE ignores it */
        $client->request('GET', $editUrl);
        self::submitInvalidForm($client, 'Save', $invalidData);

        /* Make sure DELETE doesn't work with wrong CSRF even if form is OK */
        $client->request('GET', $editUrl);
        self::submitInvalidForm($client, 'Delete', $invalidCsrf);

        /* Make sure DELETE works with invalid data as long as CSRF is OK */
        self::submitValidForm($client, 'Delete', $invalidData);
    }
}
