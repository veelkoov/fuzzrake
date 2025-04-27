<?php

declare(strict_types=1);

namespace App\Tests\Controller\IuForm;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Tests\Controller\Traits\FormsChoicesValuesAndLabelsTestTrait;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @medium
 */
class IuFormControllerTest extends FuzzrakeWebTestCase
{
    use IuFormTrait;
    use FormsChoicesValuesAndLabelsTestTrait;

    public function testIuFormLoadsForExistingCreators(): void
    {
        self::addSimpleCreator();

        self::$client->request('GET', '/iu_form/start/TEST');
        static::assertEquals(404, self::$client->getResponse()->getStatusCode());
        self::$client->request('GET', '/iu_form/start/TEST002');
        static::assertEquals(404, self::$client->getResponse()->getStatusCode());
        self::$client->request('GET', '/iu_form/start/TEST000');
        static::assertEquals(200, self::$client->getResponse()->getStatusCode());
    }

    public function testSubmittingEmptyDoesnt500(): void
    {
        self::$client->request('GET', '/iu_form/start');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form();
        self::submitInvalid($form);
    }

    /**
     * @param list<array{value: string, label: string}> $choices
     *
     * @dataProvider formsChoicesValuesAndLabelsDataProvider
     */
    public function testFormsDisplayChoicesProperlyWithValuesAndLabels(array $choices): void
    {
        self::$client->request('GET', '/iu_form/start');
        self::skipRules();
        $crawler = self::$client->getCrawler();

        foreach ($choices as $choice) {
            $label = $choice['label'];
            $value = $choice['value'];

            $inputXPath = "//input[@type = \"checkbox\"][@value = \"$value\"]";
            self::assertCount(1, $crawler->filterXPath($inputXPath), "Absent: $inputXPath");

            $labelXPath = "//label[text() = \"$label\"]";
            self::assertCount(1, $crawler->filterXPath($labelXPath), "Absent: $labelXPath");
        }
    }

    public function testOneCreatorCannotUseOtherCreatorsCreatorId(): void
    {
        self::persistAndFlush(
            self::getCreator(creatorId: 'TEST002'),
            self::getCreator(creatorId: 'TEST001', password: 'aBcDeFgH1324', contactAllowed: ContactPermit::NO,
                ages: Ages::ADULTS, nsfwWebsite: false, nsfwSocial: false, doesNsfw: false, worksWithMinors: false),
        );

        self::$client->request('GET', '/iu_form/start/TEST001');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[creatorId]' => 'TEST002',
            'iu_form[password]' => 'aBcDeFgH1324',
            $this->getCaptchaFieldName('right') => 'right',
        ]);
        self::submitInvalid($form);
        self::assertSelectorTextContains('#iu_form_creatorId_help + .invalid-feedback',
            'This maker ID has been already used by another maker.');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[creatorId]' => 'TEST003',
            'iu_form[password]' => 'aBcDeFgH1324',
        ]);
        self::submitValid($form);
    }

    public function testNewCreatorCannotUseOtherCreatorsCreatorId(): void
    {
        self::persistAndFlush(
            self::getCreator(creatorId: 'TEST001'),
        );

        self::$client->request('GET', '/iu_form/start');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[creatorId]'       => 'TEST001',
            'iu_form[name]'            => 'test-maker-555',
            'iu_form[country]'         => 'Finland',
            'iu_form[ages]'            => 'MINORS',
            'iu_form[nsfwWebsite]'     => 'NO',
            'iu_form[nsfwSocial]'      => 'NO',
            'iu_form[worksWithMinors]' => 'NO',
            'iu_form[contactAllowed]'  => 'NO',
            'iu_form[password]'        => 'aBcDeFgH1324',
            $this->getCaptchaFieldName('right') => 'right',
        ]);
        self::submitInvalid($form);
        self::assertSelectorTextContains('#iu_form_creatorId_help + .invalid-feedback',
            'This maker ID has been already used by another maker.');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[creatorId]' => 'TEST002',
            'iu_form[password]' => 'aBcDeFgH1324',
        ]);
        self::submitValid($form);
    }

    public function testEmailAddressNotShownForNewCreator(): void
    {
        self::$client->request('GET', '/iu_form/start');
        self::skipRules();
        self::assertSelectorTextNotContains('#iu_form_emailAddress_help', 'Your current email address is');
    }

    /**
     * LEGACY: grep-code-invalid-email-addresses This case is a result of past bad design decision.
     */
    public function testInvalidEmailAddressNotShownForExistingCreator(): void
    {
        self::persistAndFlush(self::getCreator(creatorId: 'TEST001', emailAddress: 'garbage'));
        self::$client->request('GET', '/iu_form/start/TEST001');
        self::skipRules();
        self::assertSelectorTextNotContains('#iu_form_emailAddress_help', 'Your current email address is');
    }

    public function testPreviousEmailAddressShownForExistingCreator(): void
    {
        self::persistAndFlush(self::getCreator(creatorId: 'TEST001', emailAddress: 'valid@example.com'));
        self::$client->request('GET', '/iu_form/start/TEST001');
        self::skipRules();
        self::assertSelectorTextContains('#iu_form_emailAddress_help', 'Your current email address is v***d@e*********m');
    }
}
