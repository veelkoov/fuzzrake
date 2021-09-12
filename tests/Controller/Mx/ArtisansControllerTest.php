<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\DbEnabledWebTestCase;

class ArtisansControllerTest extends DbEnabledWebTestCase
{
    public function testNewArtisan()
    {
        $client = static::createClient();

        $client->request('GET', '/mx/artisans/new');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEditArtisan(): void
    {
        $client = static::createClient();

        $artisan = self::getArtisan(password: 'password-555');
        self::persistAndFlush($artisan);

        self::assertTrue(password_verify('password-555', $artisan->getPassword()), 'Hashed password do not match.');

        $crawler = $client->request('GET', "/mx/artisans/{$artisan->getId()}/edit");
        self::assertResponseStatusCodeSame(200);

        $form = $crawler->selectButton('Save')->form([
            'artisan[makerId]' => 'MAKERID',
        ]);

        $client->submit($form);
        $client->followRedirect();

        unset($artisan);
        self::getEM()->clear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertTrue(password_verify('password-555', $artisan->getPassword()), 'Password has changed.');
        self::assertNull($artisan->getWorksWithMinors(), 'Works with minors has changed.');
        self::assertNull($artisan->getIsMinor(), 'Is minor has changed.');
    }

    public function testSubmittingEmptyDoesnt500(): void
    {
        $client = static::createClient();

        $client->request('GET', '/mx/artisans/new');
        $form = $client->getCrawler()->selectButton('Save')->form();
        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
    }
}
