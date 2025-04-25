<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @medium
 */
class EventsControllerWithEMTest extends WebTestCaseWithEM
{
    private KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'testing',
        ]);
    }

    public function testEventAddAndEdit(): void
    {
        $this->client->request('GET', '/mx/events/new');
        static::assertResponseStatusCodeIs($this->client, 200);

        $this->client->submitForm('Save', [
            'event[newCreatorsCount]'             => 2,
            'event[updatedCreatorsCount]'         => 0,
            'event[reportedUpdatedCreatorsCount]' => 0,
        ]);

        $this->client->followRedirect();
        static::assertResponseStatusCodeIs($this->client, 200);
        static::assertSelectorTextContains('#events-list p', '2 new makers based on received I/U requests.');

        $this->client->click($this->client->getCrawler()->filter('i.fa-edit')->ancestors()->link());
        static::assertResponseStatusCodeIs($this->client, 200);

        $this->client->submitForm('Save', [
            'event[newCreatorsCount]'             => 0,
            'event[updatedCreatorsCount]'         => 1,
            'event[reportedUpdatedCreatorsCount]' => 1,
        ]);

        $this->client->followRedirect();
        static::assertResponseStatusCodeIs($this->client, 200);
        static::assertSelectorTextContains('#events-list p', '1 updated maker based on received I/U request.');
        static::assertSelectorTextContains('#events-list p', '1 maker updated after report sent by a visitor(s). Thank you for your contribution!');
    }
}
