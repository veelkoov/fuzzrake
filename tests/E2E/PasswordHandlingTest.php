<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Utils\DataInputException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\Mapping\MappingException;
use JsonException;

class PasswordHandlingTest extends IuSubmissionAbstractTest
{
    /**
     * @throws DataInputException|JsonException|ORMException|MappingException
     */
    public function testNewMakerPasswordIsHashed(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/fill');
        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'           => 'Maker',
            'iu_form[country]'        => 'FI',
            'iu_form[makerId]'        => 'MAKERID',
            'iu_form[contactAllowed]' => 'NO',
            'iu_form[password]'       => 'some-password',
        ]);
        $client->submit($form);
        $client->followRedirect();

        self::assertSelectorTextContains('h4', 'Your submission has been recorded!');

        self::performImport(true);
        self::getEM()->flush();
        self::getEM()->clear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertTrue(password_verify('some-password', $artisan->getPassword())); // Fails on plaintext
    }

    /**
     * @throws MappingException|ORMException|DataInputException|JsonException
     */
    public function testMakerUpdatedWithCorrectPasswordHasUnchangedHash(): void
    {
        $client = static::createClient();

        $artisan = self::getArtisan(name: 'Old name', makerId: 'MAKERID', password: 'known-password', contactAllowed: 'NO');
        self::persistAndFlush($artisan);
        $oldHash = $artisan->getPassword();
        unset($artisan);

        $client->request('GET', '/iu_form/fill/MAKERID');
        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'     => 'New name',
            'iu_form[password]' => 'known-password',
        ]);
        $client->submit($form);
        $client->followRedirect();

        self::assertSelectorTextContains('h4', 'Your submission has been recorded!');

        self::performImport(true);
        self::getEM()->flush();
        self::getEM()->clear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertEquals($oldHash, $artisan->getPassword(), 'The password hash has changed'); // Fails on plaintext
        self::assertEquals('New name', $artisan->getName(), 'The update did not actually happen');
    }

    /**
     * @throws MappingException|ORMException|DataInputException|JsonException
     */
    public function testMakerUpdatedWithPasswordChangeHasUpdatedHash(): void
    {
        $client = static::createClient();

        $artisan = self::getArtisan(name: 'Old name', makerId: 'MAKERID', password: 'old-password', contactAllowed: 'NO');
        self::persistAndFlush($artisan);
        $oldHash = $artisan->getPassword();
        unset($artisan);

        $client->request('GET', '/iu_form/fill/MAKERID');
        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'     => 'New name',
            'iu_form[password]' => 'new-password',
        ]);
        $client->submit($form);
        $client->followRedirect();

        self::assertSelectorTextContains('h4', 'Your submission has been recorded, but...');

        self::performImport(true);
        self::getEM()->flush();
        self::getEM()->clear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertNotEquals($oldHash, $artisan->getPassword(), 'The password was not changed');
        self::assertEquals('New name', $artisan->getName(), 'The update did not actually happen');
        self::assertTrue(password_verify('new-password', $artisan->getPassword()), 'Updated password fails');
    }

    /**
     * @throws MappingException|ORMException|DataInputException|JsonException
     */
    public function testNotAcceptedMakerWithWrongPasswordDoesntGetUpdated(): void
    {
        $client = static::createClient();

        $artisan = self::getArtisan(name: 'Old name', makerId: 'MAKERID', password: 'old-password', contactAllowed: 'NO');
        self::persistAndFlush($artisan);
        $oldHash = $artisan->getPassword();
        unset($artisan);

        $client->request('GET', '/iu_form/fill/MAKERID');
        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]'     => 'New name',
            'iu_form[password]' => 'new-password',
        ]);
        $client->submit($form);
        $client->followRedirect();

        self::assertSelectorTextContains('h4', 'Your submission has been recorded, but...');

        self::performImport(false);
        self::getEM()->flush();
        self::getEM()->clear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertEquals($oldHash, $artisan->getPassword(), 'The password was actually changed');
        self::assertEquals('Old name', $artisan->getName(), 'The update actually happened');
    }
}
