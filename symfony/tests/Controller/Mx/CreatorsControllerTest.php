<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\Controller\Traits\FormsChoicesValuesAndLabelsTestTrait;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @medium
 */
class CreatorsControllerTest extends FuzzrakeWebTestCase
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
        $crawler = $this->client->request('GET', '/mx/creators/new');
        self::assertResponseStatusCodeIs($this->client, 200);

        foreach ($choices as $choice) {
            $label = $choice['label'];
            $value = $choice['value'];

            $optionXPath = "//option[@value = \"$value\"][text() = \"$label\"]";
            self::assertCount(1, $crawler->filterXPath($optionXPath), "Absent: $optionXPath");
        }
    }

    public function testNewCreator(): void
    {
        $crawler = $this->client->request('GET', '/mx/creators/new');
        self::assertResponseStatusCodeIs($this->client, 200);

        $form = $crawler->selectButton('Save')->form([
            'creator[creatorId]' => 'TEST001',
            'creator[name]' => 'New creator',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();
        self::assertResponseStatusCodeIs($this->client, 200);

        self::clear();

        self::findCreatorByCreatorId('TEST001');
    }

    public function testEditCreator(): void
    {
        /** @noinspection PhpRedundantOptionalArgumentInspection Make sure defaults for ages and worksWithMinors don't change. */
        $creator = self::getCreator(creatorId: 'TEST001', password: 'password-555', ages: null, worksWithMinors: null);
        self::persistAndFlush($creator);

        self::assertTrue(password_verify('password-555', $creator->getPassword()), 'Hashed password do not match.');

        $crawler = $this->client->request('GET', '/mx/creators/TEST001/edit');
        self::assertResponseStatusCodeIs($this->client, 200);

        $form = $crawler->selectButton('Save')->form([
            'creator[creatorId]' => 'TEST001',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();
        self::assertResponseStatusCodeIs($this->client, 200);

        unset($creator);
        self::clear();

        $creator = self::findCreatorByCreatorId('TEST001');
        self::assertTrue(password_verify('password-555', $creator->getPassword()), 'Password has changed.');
        self::assertNull($creator->getWorksWithMinors(), 'Works with minors has changed.');
        self::assertNull($creator->getAges(), 'Ages has changed.');
    }

    public function testDeleteCreatorAnd404Response(): void
    {
        $creator = self::getCreator(creatorId: 'TEST001');
        self::persistAndFlush($creator);

        $crawler = $this->client->request('GET', '/mx/creators/TEST001/edit');
        self::assertResponseStatusCodeIs($this->client, 200);

        $form = $crawler->selectButton('Delete')->form();
        $this->client->submit($form);
        $this->client->followRedirect();
        self::assertResponseStatusCodeIs($this->client, 200);

        self::clear();

        $this->client->request('GET', '/mx/creators/TEST001/edit');
        self::assertResponseStatusCodeIs($this->client, 404);
    }

    public function testSubmittingEmptyDoesnt500(): void
    {
        $this->client->request('GET', '/mx/creators/new');
        $form = $this->client->getCrawler()->selectButton('Save')->form();
        $this->client->submit($form);

        self::assertResponseStatusCodeIs($this->client, 422);
    }

    /**
     * @dataProvider contactUpdatesDataProvider
     */
    public function testContactUpdates(string $was, string $set, string $check): void
    {
        $creator = self::getCreator(creatorId: 'TEST001');
        $creator->setEmailAddress($was);
        self::persistAndFlush($creator);

        $this->client->request('GET', '/mx/creators/TEST001/edit');

        self::submitValidForm($this->client, 'Save', [
            'creator[emailAddress]' => $set,
        ]);

        unset($creator);
        self::clear();

        $creator = self::findCreatorByCreatorId('TEST001');
        self::assertEquals($check, $creator->getEmailAddress());
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
