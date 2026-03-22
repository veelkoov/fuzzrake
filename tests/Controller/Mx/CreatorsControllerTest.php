<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\Controller\Traits\FormsChoicesValuesAndLabelsTestTrait;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class CreatorsControllerTest extends FuzzrakeWebTestCase
{
    use FormsChoicesValuesAndLabelsTestTrait;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::haveAnAdminUser();
        self::loginAdminUser();
    }

    /**
     * @param list<array{value: string, label: string}> $choices
     */
    #[DataProvider('formsChoicesValuesAndLabelsDataProvider')]
    public function testFormsChoicesValuesAndLabels(array $choices): void
    {
        $crawler = self::$client->request('GET', '/mx/creators/new');
        self::assertResponseStatusCodeIs(200);

        foreach ($choices as $choice) {
            $label = $choice['label'];
            $value = $choice['value'];

            $optionXPath = "//option[@value = \"$value\"][text() = \"$label\"]";
            self::assertCount(1, $crawler->filterXPath($optionXPath), "Absent: $optionXPath");
        }
    }

    public function testNewCreator(): void
    {
        $crawler = self::$client->request('GET', '/mx/creators/new');
        self::assertResponseStatusCodeIs(200);

        $form = $crawler->selectButton('Save')->form([
            'creator[creatorId]' => 'TEST001',
            'creator[name]' => 'New creator',
        ]);

        self::$client->submit($form);
        self::$client->followRedirect();
        self::assertResponseStatusCodeIs(200);

        self::clear();

        self::findCreatorByCreatorId('TEST001');
    }

    public function testEditCreator(): void
    {
        /** @noinspection PhpRedundantOptionalArgumentInspection Make sure defaults for ages and worksWithMinors don't change. */
        $creator = self::getCreator(creatorId: 'TEST001', ages: null, worksWithMinors: null);
        self::persistAndFlush($creator);

        $crawler = self::$client->request('GET', '/mx/creators/TEST001/edit');
        self::assertResponseStatusCodeIs(200);

        $form = $crawler->selectButton('Save')->form([
            'creator[creatorId]' => 'TEST001',
        ]);

        self::$client->submit($form);
        self::$client->followRedirect();
        self::assertResponseStatusCodeIs(200);

        unset($creator);
        self::clear();

        $creator = self::findCreatorByCreatorId('TEST001');
        self::assertNull($creator->getWorksWithMinors(), 'Works with minors has changed.');
        self::assertNull($creator->getAges(), 'Ages has changed.');
    }

    public function testDeleteCreatorAnd404Response(): void
    {
        $creator = self::getCreator(creatorId: 'TEST001');
        self::persistAndFlush($creator);

        $crawler = self::$client->request('GET', '/mx/creators/TEST001/edit');
        self::assertResponseStatusCodeIs(200);

        $form = $crawler->selectButton('Delete')->form();
        self::$client->submit($form);
        self::$client->followRedirect();
        self::assertResponseStatusCodeIs(200);

        self::clear();

        self::$client->request('GET', '/mx/creators/TEST001/edit');
        self::assertResponseStatusCodeIs(404);
    }

    public function testSubmittingEmptyDoesnt500(): void
    {
        self::$client->request('GET', '/mx/creators/new');
        $form = self::$client->getCrawler()->selectButton('Save')->form();
        self::$client->submit($form);

        self::assertResponseStatusCodeIs(422);
    }
}
