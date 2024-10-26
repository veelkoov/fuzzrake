<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\Controller\Traits\FormsChoicesValuesAndLabelsTestTrait;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;

/**
 * @medium
 */
class ArtisansControllerWithEMTest extends WebTestCaseWithEM
{
    use FormsChoicesValuesAndLabelsTestTrait;

    /**
     * @param list<array{value: string, label: string}> $choices
     *
     * @dataProvider formsChoicesValuesAndLabelsDataProvider
     */
    public function testFormsChoicesValuesAndLabels(array $choices): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/mx/artisans/new');
        self::assertResponseStatusCodeIs($client, 200);

        foreach ($choices as $choice) {
            $label = $choice['label'];
            $value = $choice['value'];

            $optionXPath = "//option[@value = \"$value\"][text() = \"$label\"]";
            self::assertCount(1, $crawler->filterXPath($optionXPath), "Absent: $optionXPath");
        }
    }

    public function testNewArtisan(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/mx/artisans/new');
        self::assertResponseStatusCodeIs($client, 200);

        $form = $crawler->selectButton('Save')->form([
            'artisan[makerId]' => 'MAKERID',
            'artisan[name]'    => 'New artisan',
        ]);

        $client->submit($form);
        $client->followRedirect();
        self::assertResponseStatusCodeIs($client, 200);

        self::clear();

        self::findArtisanByMakerId('MAKERID');
    }

    public function testEditArtisan(): void
    {
        $client = static::createClient();

        /** @noinspection PhpRedundantOptionalArgumentInspection Make sure defaults for ages and worksWithMinors don't change. */
        $artisan = self::getArtisan(password: 'password-555', ages: null, worksWithMinors: null);
        self::persistAndFlush($artisan);

        self::assertTrue(password_verify('password-555', $artisan->getPassword()), 'Hashed password do not match.');

        $crawler = $client->request('GET', "/mx/artisans/{$artisan->getMakerId()}/edit");
        self::assertResponseStatusCodeIs($client, 200);

        $form = $crawler->selectButton('Save')->form([
            'artisan[makerId]' => 'MAKERID',
        ]);

        $client->submit($form);
        $client->followRedirect();
        self::assertResponseStatusCodeIs($client, 200);

        unset($artisan);
        self::clear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertTrue(password_verify('password-555', $artisan->getPassword()), 'Password has changed.');
        self::assertNull($artisan->getWorksWithMinors(), 'Works with minors has changed.');
        self::assertNull($artisan->getAges(), 'Ages has changed.');
    }

    public function testDeleteArtisanAnd404Response(): void
    {
        $client = static::createClient();

        $artisan = self::getArtisan(makerId: 'MAKERID');
        self::persistAndFlush($artisan);

        $crawler = $client->request('GET', "/mx/artisans/{$artisan->getMakerId()}/edit");
        self::assertResponseStatusCodeIs($client, 200);

        $form = $crawler->selectButton('Delete')->form();
        $client->submit($form);
        $client->followRedirect();
        self::assertResponseStatusCodeIs($client, 200);

        self::clear();

        $client->request('GET', "/mx/artisans/{$artisan->getMakerId()}/edit");
        self::assertResponseStatusCodeIs($client, 404);
    }

    public function testSubmittingEmptyDoesnt500(): void
    {
        $client = static::createClient();

        $client->request('GET', '/mx/artisans/new');
        $form = $client->getCrawler()->selectButton('Save')->form();
        $client->submit($form);

        self::assertResponseStatusCodeIs($client, 422);
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
            ->setEmailAddress($data['init_original'])
            ->setEmailAddressObfuscated($data['init_obfuscated']);
        self::persistAndFlush($artisan);

        $client->request('GET', "/mx/artisans/{$artisan->getMakerId()}/edit");

        self::submitValidForm($client, 'Save', [
            'artisan[emailAddressObfuscated]' => $data['set_obfuscated'],
            'artisan[emailAddress]'           => $data['set_original'],
        ]);

        unset($artisan);
        self::clear();

        $artisan = self::findArtisanByMakerId('TEST000');
        self::assertEquals($data['check_original'], $artisan->getEmailAddress(), 'Original email differs');
        self::assertEquals($data['check_obfuscated'], $artisan->getEmailAddressObfuscated(), 'Obfuscated email differs');
    }

    /**
     * @return list<array{array{init_original: string, init_obfuscated: string, set_original: string, set_obfuscated: string, check_original: string, check_obfuscated: string}}>
     */
    public function contactUpdatesDataProvider(): array // FIXME: Validate the test thingys make sense
    {
        return [
            [[
                'init_original'   => '',
                'init_obfuscated' => '',

                'set_original'    => 'some-email@somedomain.fi',
                'set_obfuscated'  => '',

                'check_original'   => 'some-email@somedomain.fi',
                'check_obfuscated' => 'so******il@som*******.fi',
            ]],
            [[
                'init_original'   => '',
                'init_obfuscated' => '',

                'set_original'    => '',
                'set_obfuscated'  => 'some-email@somedomain.fi',

                'check_original'   => '',
                'check_obfuscated' => 'some-email@somedomain.fi',
            ]],
            [[
                'init_original'   => 'some-email@somedomain.fi',
                'init_obfuscated' => 'so******il@som*******.fi',

                'set_original'    => '@some_telegram',
                'set_obfuscated'  => 'so******il@som*******.fi',

                'check_original'   => '@some_telegram',
                'check_obfuscated' => '@som*******ram',
            ]],
            [[
                'init_original'   => 'some-email@somedomain.fi',
                'init_obfuscated' => 'so******il@som*******.fi',

                'set_original'    => 'some-email@somedomain.fi',
                'set_obfuscated'  => 'Please update, original was: E-MAIL: so******il@som*******.fi',

                'check_original'   => 'some-email@somedomain.fi',
                'check_obfuscated' => 'Please update, original was: E-MAIL: so******il@som*******.fi',
            ]],
            [[
                'init_original'   => 'some-email@somedomain.fi',
                'init_obfuscated' => 'Please update, original was: E-MAIL: so******il@som*******.fi',

                'set_original'    => 'some-email@somedomain.fi',
                'set_obfuscated'  => 'Please update, original was: E-MAIL: so******il@som*******.fi',

                'check_original'   => 'some-email@somedomain.fi',
                'check_obfuscated' => 'Please update, original was: E-MAIL: so******il@som*******.fi',
            ]],
        ];
    }
}
