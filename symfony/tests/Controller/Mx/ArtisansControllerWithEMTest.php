<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\Controller\Traits\FormsChoicesValuesAndLabelsTestTrait;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use TRegx\PhpUnit\DataProviders\DataProvider;

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
     * @dataProvider contactUpdatesDataProvider
     */
    public function testContactUpdates(string $was, string $set, string $check): void
    {
        $artisan = self::getArtisan();
        $artisan->setEmailAddress($was);
        self::persistAndFlush($artisan);

        $this->client->request('GET', "/mx/artisans/{$artisan->getMakerId()}/edit");

        self::submitValidForm($this->client, 'Save', [
            'artisan[emailAddress]' => $set,
        ]);

        unset($artisan);
        self::clear();

        $artisan = self::findArtisanByMakerId('TEST000');
        self::assertEquals($check, $artisan->getEmailAddress());
    }

    public function contactUpdatesDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            ['',                         '',                          ''],
            ['garbage',                  'garbage',                   'garbage'],
            ['garbage',                  'some-email@somedomain.fi',  'some-email@somedomain.fi'],
            ['',                         'some-email@somedomain.fi',  'some-email@somedomain.fi'],
            ['some-email@somedomain.fi', 'updated-email@example.com', 'updated-email@example.com'],
            ['some-email@somedomain.fi', 'some-email@somedomain.fi',  'some-email@somedomain.fi'],
        );
    }
}
