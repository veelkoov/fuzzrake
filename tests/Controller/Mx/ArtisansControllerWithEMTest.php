<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;

/**
 * @medium
 */
class ArtisansControllerWithEMTest extends WebTestCaseWithEM
{
    public function testNewArtisan(): void
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
        self::clear();

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

    /**
     * @param string[] $data
     *
     * @dataProvider contactUpdatesDataProvider
     */
    public function testContactUpdates(array $data): void
    {
        $client = static::createClient();

        $artisan = self::getArtisan();
        $artisan
            ->setContactInfoOriginal($data['init_original'])
            ->setContactInfoObfuscated($data['init_obfuscated'])
            ->setContactMethod($data['init_method'])
            ->setContactAddressPlain($data['init_address']);
        self::persistAndFlush($artisan);

        $client->request('GET', "/mx/artisans/{$artisan->getId()}/edit");

        self::submitValidForm($client, 'Save', [
            'artisan[contactInfoObfuscated]' => $data['set_obfuscated'],
            'artisan[contactInfoOriginal]'   => $data['set_original'],
        ]);

        unset($artisan);
        self::clear();

        $artisan = self::findArtisanByMakerId('TEST000');
        self::assertEquals($data['check_original'], $artisan->getContactInfoOriginal(), 'Original info differs');
        self::assertEquals($data['check_obfuscated'], $artisan->getContactInfoObfuscated(), 'Obfuscated info differs');
        self::assertEquals($data['check_method'], $artisan->getContactMethod(), 'Method differs');
        self::assertEquals($data['check_address'], $artisan->getContactAddressPlain(), 'Address differs');
    }

    public function contactUpdatesDataProvider(): array // @phpstan-ignore-line
    {
        return [
            [[
                'init_original'   => '',
                'init_obfuscated' => '',
                'init_method'     => '',
                'init_address'    => '',

                'set_original'    => 'some-email@somedomain.fi',
                'set_obfuscated'  => '',

                'check_original'   => 'some-email@somedomain.fi',
                'check_obfuscated' => 'E-MAIL: so******il@som*******.fi',
                'check_method'     => 'E-MAIL',
                'check_address'    => 'some-email@somedomain.fi',
            ]],
            [[
                'init_original'   => '',
                'init_obfuscated' => '',
                'init_method'     => '',
                'init_address'    => '',

                'set_original'    => '',
                'set_obfuscated'  => 'some-email@somedomain.fi',

                'check_original'   => '',
                'check_obfuscated' => 'some-email@somedomain.fi',
                'check_method'     => '',
                'check_address'    => '',
            ]],
            [[
                'init_original'   => 'some-email@somedomain.fi',
                'init_obfuscated' => 'E-MAIL: so******il@som*******.fi',
                'init_method'     => 'E-MAIL',
                'init_address'    => 'some-email@somedomain.fi',

                'set_original'    => 'Telegram: @some_telegram',
                'set_obfuscated'  => 'E-MAIL: so******il@som*******.fi',

                'check_original'   => 'Telegram: @some_telegram',
                'check_obfuscated' => 'TELEGRAM: @som*******ram',
                'check_method'     => 'TELEGRAM',
                'check_address'    => '@some_telegram',
            ]],
            [[
                'init_original'   => 'some-email@somedomain.fi',
                'init_obfuscated' => 'E-MAIL: so******il@som*******.fi',
                'init_method'     => 'E-MAIL',
                'init_address'    => 'some-email@somedomain.fi',

                'set_original'    => 'some-email@somedomain.fi',
                'set_obfuscated'  => 'Please update, original was: E-MAIL: so******il@som*******.fi',

                'check_original'   => 'some-email@somedomain.fi',
                'check_obfuscated' => 'Please update, original was: E-MAIL: so******il@som*******.fi',
                'check_method'     => 'E-MAIL',
                'check_address'    => 'some-email@somedomain.fi',
            ]],
            [[
                'init_original'   => 'some-email@somedomain.fi',
                'init_obfuscated' => 'Please update, original was: E-MAIL: so******il@som*******.fi',
                'init_method'     => 'E-MAIL',
                'init_address'    => 'some-email@somedomain.fi',

                'set_original'    => 'some-email@somedomain.fi',
                'set_obfuscated'  => 'Please update, original was: E-MAIL: so******il@som*******.fi',

                'check_original'   => 'some-email@somedomain.fi',
                'check_obfuscated' => 'Please update, original was: E-MAIL: so******il@som*******.fi',
                'check_method'     => 'E-MAIL',
                'check_address'    => 'some-email@somedomain.fi',
            ]],
        ];
    }
}
