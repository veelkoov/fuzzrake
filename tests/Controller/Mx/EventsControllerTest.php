<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

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

        self::$client->setServerParameters([
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'testing',
        ]);
    }

    public function testEventAddAndEdit(): void
    {
        self::$client->request('GET', '/mx/events/new');
        self::assertResponseStatusCodeIs(200);

        self::$client->submitForm('Save', [
            'event[newCreatorsCount]'             => 2,
            'event[updatedCreatorsCount]'         => 0,
            'event[reportedUpdatedCreatorsCount]' => 0,
        ]);

        self::$client->followRedirect();
        self::assertResponseStatusCodeIs(200);
        static::assertSelectorTextContains('#events-list p', '2 new makers based on received I/U requests.');

        self::$client->click(self::$client->getCrawler()->filter('i.fa-edit')->ancestors()->link());
        self::assertResponseStatusCodeIs(200);

        self::$client->submitForm('Save', [
            'event[newCreatorsCount]'             => 0,
            'event[updatedCreatorsCount]'         => 1,
            'event[reportedUpdatedCreatorsCount]' => 1,
        ]);

        self::$client->followRedirect();
        self::assertResponseStatusCodeIs(200);
        static::assertSelectorTextContains('#events-list p', '1 updated maker based on received I/U request.');
        static::assertSelectorTextContains('#events-list p', '1 maker updated after report sent by a visitor(s). Thank you for your contribution!');
    }
}
