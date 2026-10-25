<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Mx;

use App\Entity\Event;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use Override;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class EventsControllerTest extends FuzzrakeWebTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::haveAnAdminUser();
        self::loginAdminUser();
    }

    public function testEventCreating(): void
    {
        self::$client->request('GET', '/mx/events/new');
        self::assertResponseStatusCodeIs(200);

        self::submitValidForm('Save', [
            'event[newCreatorsCount]'             => 2,
            'event[updatedCreatorsCount]'         => 0,
            'event[reportedUpdatedCreatorsCount]' => 0,
        ]);

        self::assertResponseStatusCodeIs(200);
        self::assertSelectorTextContains('#events-list p', '2 new makers based on received I/U requests.');
    }

    public function testEventEditing(): void
    {
        $event = new Event()->setNewCreatorsCount(2);
        self::persistAndFlush($event);

        self::$client->request('GET', "/mx/events/{$event->getId()}/edit");
        self::assertResponseStatusCodeIs(200);

        self::submitValidForm('Save', [
            'event[newCreatorsCount]'             => 0,
            'event[updatedCreatorsCount]'         => 1,
            'event[reportedUpdatedCreatorsCount]' => 1,
        ]);

        self::assertResponseStatusCodeIs(200);
        self::assertSelectorTextContains('#events-list p', '1 updated maker based on received I/U request.');
        self::assertSelectorTextContains('#events-list p', '1 maker updated after report sent by a visitor(s). Thank you for your contribution!');
    }

    public function testEventDeleting(): void
    {
        $event = new Event()->setNewCreatorsCount(2);
        self::persistAndFlush($event);

        self::$client->request('GET', "/mx/events/{$event->getId()}/edit");
        self::assertResponseStatusCodeIs(200);

        self::submitValidForm('Delete', []);

        self::assertResponseStatusCodeIs(200);
        self::assertSelectorNotExists('#events-list p');
    }
}
