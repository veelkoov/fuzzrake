<?php

declare(strict_types=1);

namespace App\Tests\Controller\IuForm;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Tests\Controller\Traits\FormsChoicesValuesAndLabelsTestTrait;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @medium
 */
class IuFormControllerWithEMTest extends WebTestCaseWithEM
{
    use IuFormTrait;
    use FormsChoicesValuesAndLabelsTestTrait;

    public function testIuFormLoadsForExistingMakers(): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/iu_form/start/TEST');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('GET', '/iu_form/start/TEST002');
        static::assertEquals(404, $client->getResponse()->getStatusCode());
        $client->request('GET', '/iu_form/start/TEST000');
        static::assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testSubmittingEmptyDoesnt500(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form();
        self::submitInvalid($client, $form);
    }

    public function testCannotSkipCaptcha(): void
    {
        $client = static::createClient();
        $client->followRedirects(true);

        $crawler = $client->request('GET', '/iu_form/data');
        $uri = $crawler->getUri();

        self::assertNotNull($uri);
        self::assertMatchesRegularExpression('#/iu_form/start$#', $uri);
    }

    /**
     * @param list<array{value: string, label: string}> $choices
     *
     * @dataProvider formsChoicesValuesAndLabelsDataProvider
     */
    public function testFormsDisplayChoicesProperlyWithValuesAndLabels(array $choices): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);
        $crawler = $client->getCrawler();

        foreach ($choices as $choice) {
            $label = $choice['label'];
            $value = $choice['value'];

            $inputXPath = "//input[@type = \"checkbox\"][@value = \"$value\"]";
            self::assertCount(1, $crawler->filterXPath($inputXPath), "Absent: $inputXPath");

            $labelXPath = "//label[text() = \"$label\"]";
            self::assertCount(1, $crawler->filterXPath($labelXPath), "Absent: $labelXPath");
        }
    }

    public function testOneMakerCannotUseOtherMakersMakerId(): void
    {
        $client = static::createClient();

        self::persistAndFlush(
            self::getArtisan(makerId: 'OTHERID'),
            self::getArtisan(makerId: 'MAKERID', password: 'aBcDeFgH1324', contactAllowed: ContactPermit::NO,
                ages: Ages::ADULTS, nsfwWebsite: false, nsfwSocial: false, doesNsfw: false, worksWithMinors: false),
        );

        $client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[makerId]' => 'OTHERID',
            'iu_form[password]' => 'aBcDeFgH1324',
        ]);
        self::submitInvalid($client, $form);
        self::assertSelectorTextContains('#iu_form_makerId_help + .invalid-feedback',
            'This maker ID has been already used by another maker.');

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[makerId]' => 'ANOTHER',
            'iu_form[password]' => 'aBcDeFgH1324',
        ]);
        self::submitValid($client, $form);
    }

    public function testNewMakerCannotUseOtherMakersMakerId(): void
    {
        $client = static::createClient();

        self::persistAndFlush(
            self::getArtisan(makerId: 'OTHERID'),
        );

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[makerId]'         => 'OTHERID',
            'iu_form[name]'            => 'test-maker-555',
            'iu_form[country]'         => 'Finland',
            'iu_form[ages]'            => 'MINORS',
            'iu_form[nsfwWebsite]'     => 'NO',
            'iu_form[nsfwSocial]'      => 'NO',
            'iu_form[worksWithMinors]' => 'NO',
            'iu_form[contactAllowed]'  => 'NO',
            'iu_form[password]'        => 'aBcDeFgH1324',
        ]);
        self::submitInvalid($client, $form);
        self::assertSelectorTextContains('#iu_form_makerId_help + .invalid-feedback',
            'This maker ID has been already used by another maker.');

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'iu_form[makerId]' => 'ANOTHER',
            'iu_form[password]' => 'aBcDeFgH1324',
        ]);
        self::submitValid($client, $form);
    }
}
