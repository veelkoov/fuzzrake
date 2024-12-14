<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\Controller\Traits\FormsChoicesValuesAndLabelsTestTrait;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @medium
 */
class ArtisansControllerWithEMTest extends WebTestCaseWithEM
{
    use FormsChoicesValuesAndLabelsTestTrait;

    private KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'testing',
        ]);
    }

    /**
     * @param list<array{value: string, label: string}> $choices
     *
     * @dataProvider formsChoicesValuesAndLabelsDataProvider
     */
    public function testFormsChoicesValuesAndLabels(array $choices): void
    {
        $crawler = $this->client->request('GET', '/mx/artisans/new');
        self::assertResponseStatusCodeIs($this->client, 200);

        foreach ($choices as $choice) {
            $label = $choice['label'];
            $value = $choice['value'];

            $optionXPath = "//option[@value = \"$value\"][text() = \"$label\"]";
            self::assertCount(1, $crawler->filterXPath($optionXPath), "Absent: $optionXPath");
        }
    }

    public function testNewArtisan(): void
    {
        $crawler = $this->client->request('GET', '/mx/artisans/new');
        self::assertResponseStatusCodeIs($this->client, 200);

        $form = $crawler->selectButton('Save')->form([
            'artisan[makerId]' => 'MAKERID',
            'artisan[name]'    => 'New artisan',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();
        self::assertResponseStatusCodeIs($this->client, 200);

        self::clear();

        self::findArtisanByMakerId('MAKERID');
    }

    public function testEditArtisan(): void
    {
        /** @noinspection PhpRedundantOptionalArgumentInspection Make sure defaults for ages and worksWithMinors don't change. */
        $artisan = self::getArtisan(password: 'password-555', ages: null, worksWithMinors: null);
        self::persistAndFlush($artisan);

        self::assertTrue(password_verify('password-555', $artisan->getPassword()), 'Hashed password do not match.');

        $crawler = $this->client->request('GET', "/mx/artisans/{$artisan->getMakerId()}/edit");
        self::assertResponseStatusCodeIs($this->client, 200);

        $form = $crawler->selectButton('Save')->form([
            'artisan[makerId]' => 'MAKERID',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();
        self::assertResponseStatusCodeIs($this->client, 200);

        unset($artisan);
        self::clear();

        $artisan = self::findArtisanByMakerId('MAKERID');
        self::assertTrue(password_verify('password-555', $artisan->getPassword()), 'Password has changed.');
        self::assertNull($artisan->getWorksWithMinors(), 'Works with minors has changed.');
        self::assertNull($artisan->getAges(), 'Ages has changed.');
    }

    public function testDeleteArtisanAnd404Response(): void
    {
        $artisan = self::getArtisan(makerId: 'MAKERID');
        self::persistAndFlush($artisan);

        $crawler = $this->client->request('GET', "/mx/artisans/{$artisan->getMakerId()}/edit");
        self::assertResponseStatusCodeIs($this->client, 200);

        $form = $crawler->selectButton('Delete')->form();
        $this->client->submit($form);
        $this->client->followRedirect();
        self::assertResponseStatusCodeIs($this->client, 200);

        self::clear();

        $this->client->request('GET', "/mx/artisans/{$artisan->getMakerId()}/edit");
        self::assertResponseStatusCodeIs($this->client, 404);
    }

    public function testSubmittingEmptyDoesnt500(): void
    {
        $this->client->request('GET', '/mx/artisans/new');
        $form = $this->client->getCrawler()->selectButton('Save')->form();
        $this->client->submit($form);

        self::assertResponseStatusCodeIs($this->client, 422);
    }

    /**
     * @param string[] $data
     *
     * @dataProvider contactUpdatesDataProvider
     */
    public function testContactUpdates(array $data): void
    {
        $artisan = self::getArtisan();
        $artisan
            ->setEmailAddress($data['init_original'])
            ->setEmailAddressObfuscated($data['init_obfuscated']);
        self::persistAndFlush($artisan);

        $this->client->request('GET', "/mx/artisans/{$artisan->getMakerId()}/edit");

        self::submitValidForm($this->client, 'Save', [
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
    public function contactUpdatesDataProvider(): array
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

                'set_original'    => 'updated-email@example.com',
                'set_obfuscated'  => 'so******il@som*******.fi',

                'check_original'   => 'updated-email@example.com',
                'check_obfuscated' => 'upd*******ail@ex*******om',
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
