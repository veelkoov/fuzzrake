<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Mx;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\FormsChoicesValuesAndLabelsTestTrait;
use App\Tests\TestUtils\UserCreator;
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
        $creator = UserCreator::get()->setCreatorId('TEST001');
        self::persistAndFlush($creator);

        $crawler = self::$client->request('GET', '/mx/creators/TEST001/edit');
        self::assertResponseStatusCodeIs(200);

        foreach ($choices as $choice) {
            $label = $choice['label'];
            $value = $choice['value'];

            $optionXPath = "//option[@value = \"$value\"][text() = \"$label\"]";
            self::assertCount(1, $crawler->filterXPath($optionXPath), "Absent: $optionXPath");
        }
    }

    public function testEditCreator(): void
    {
        $creator = UserCreator::get()->setCreatorId('TEST001');
        self::persistAndFlush($creator);

        $crawler = self::$client->request('GET', '/mx/creators/TEST001/edit');
        self::assertResponseStatusCodeIs(200);

        self::submitInvalidForm('Save', []); // Miss filling required name

        $form = $crawler->selectButton('Save')->form([
            'creator[name]' => 'Test creator 001',
        ]);
        self::submitValid($form);
        self::assertResponseStatusCodeIs(200);

        unset($creator);
        self::clear();

        $creator = self::findCreatorByCreatorId('TEST001');
        self::assertNull($creator->getWorksWithMinors(), 'Works with minors has changed.');
        self::assertNull($creator->getAges(), 'Ages has changed.');
        self::assertNull($creator->getHasAllergyWarning(), 'Allergy warning has changed.');
    }

    public function testDeleteCreatorAnd404Response(): void
    {
        $creator = UserCreator::get()->setCreatorId('TEST001');
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
}
