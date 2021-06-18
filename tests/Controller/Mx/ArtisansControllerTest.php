<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\DbEnabledWebTestCase;
use Doctrine\Persistence\Mapping\MappingException;

class ArtisansControllerTest extends DbEnabledWebTestCase
{
    public function testNewArtisan()
    {
        $client = static::createClient();

        $client->request('GET', '/mx/artisans/new');

        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @throws MappingException
     */
    public function testEditArtisan()
    {
        $client = static::createClient();

        $artisan = self::getArtisan(password: 'password-555');
        self::persistAndFlush($artisan);

        self::assertTrue(password_verify('password-555', $artisan->getPassword()), 'Hashed password do not match');

        $crawler = $client->request('GET', "/mx/artisans/{$artisan->getId()}/edit");
        static::assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Save')->form([
            'artisan[makerId]' => 'MAKERID',
        ]);

        $client->submit($form);
        $client->followRedirect();

        unset($artisan);
        self::getEM()->clear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertTrue(password_verify('password-555', $artisan->getPassword()), 'Password has changed');
    }
}
