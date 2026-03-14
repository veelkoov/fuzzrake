<?php

declare(strict_types=1);

namespace App\Tests\Controller\Traits;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use Override;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class ButtonClickedTraitTest extends FuzzrakeWebTestCase
{
    public function testClicked(): void
    {
        self::haveAnAdminUser();
        self::loginAdminUser();
        $event = self::addSimpleGenericEvent();

        $editUrl = '/mx/events/'.$event->getId().'/edit';

        $invalidData = ['event[newCreatorsCount]' => '-1'];
        $validData = [];

        /* Make sure SAVE button works for valid data */
        self::$client->request('GET', $editUrl);
        self::submitValidForm('Save', $validData);

        /* Make sure validation works as intended for SAVE button, so we can test if DELETE ignores it */
        self::$client->request('GET', $editUrl);
        self::submitInvalidForm('Save', $invalidData);

        /* Make sure DELETE works with invalid data */
        self::$client->request('GET', $editUrl);
        self::submitValidForm('Delete', $invalidData);
    }
}
