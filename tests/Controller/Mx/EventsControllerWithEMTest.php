<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;

/**
 * @medium
 */
class EventsControllerWithEMTest extends WebTestCaseWithEM
{
    public function testEventAddAndEdit(): void
    {
        $client = static::createClient();

        $client->request('GET', '/mx/events/new');
        static::assertResponseStatusCodeIs($client, 200);

        $client->submitForm('Save', [
            'event[newMakersCount]'             => 2,
            'event[updatedMakersCount]'         => 0,
            'event[reportedUpdatedMakersCount]' => 0,
        ]);

        $client->followRedirect();
        static::assertResponseStatusCodeIs($client, 200);
        static::assertSelectorTextContains('#events-list p', '2 new makers based on received I/U requests.');

        $client->click($client->getCrawler()->filter('i.fa-edit')->ancestors()->link());
        static::assertResponseStatusCodeIs($client, 200);

        $client->submitForm('Save', [
            'event[newMakersCount]'             => 0,
            'event[updatedMakersCount]'         => 1,
            'event[reportedUpdatedMakersCount]' => 1,
        ]);

        $client->followRedirect();
        static::assertResponseStatusCodeIs($client, 200);
        static::assertSelectorTextContains('#events-list p', '1 updated maker based on received I/U request.');
        static::assertSelectorTextContains('#events-list p', '1 maker updated after report sent by a visitor(s). Thank you for your contribution!');
    }
}
