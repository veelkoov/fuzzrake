<?php

declare(strict_types=1);

namespace App\Tests\Controller\IuForm;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Tests\Controller\Traits\FormsChoicesValuesAndLabelsTestTrait;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @medium
 */
class IuFormControllerWithEMTest extends WebTestCaseWithEM
{
    use IuFormTrait;
    use FormsChoicesValuesAndLabelsTestTrait;

    private KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testIuFormLoadsForExistingMakers(): void
    {
        self::addSimpleArtisan();

        $this->client->request('GET', '/iu_form/start/TEST');
        static::assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET', '/iu_form/start/TEST002');
        static::assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET', '/iu_form/start/TEST000');
        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmittingEmptyDoesnt500(): void
    {
        $this->client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($this->client);

        $form = $this->client->getCrawler()->selectButton('Submit')->form();
        self::submitInvalid($this->client, $form);
    }

    public function testCannotSkipCaptcha(): void
    {
        $this->client->followRedirects(true);

        $crawler = $this->client->request('GET', '/iu_form/data');
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
        $this->client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($this->client);
        $crawler = $this->client->getCrawler();

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
        self::persistAndFlush(
            self::getArtisan(makerId: 'OTHERID'),
            self::getArtisan(makerId: 'MAKERID', password: 'aBcDeFgH1324', contactAllowed: ContactPermit::NO,
                ages: Ages::ADULTS, nsfwWebsite: false, nsfwSocial: false, doesNsfw: false, worksWithMinors: false),
        );

        $this->client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($this->client);

        $form = $this->client->getCrawler()->selectButton('Submit')->form([
            'iu_form[makerId]' => 'OTHERID',
            'iu_form[password]' => 'aBcDeFgH1324',
        ]);
        self::submitInvalid($this->client, $form);
        self::assertSelectorTextContains('#iu_form_makerId_help + .invalid-feedback',
            'This maker ID has been already used by another maker.');

        $form = $this->client->getCrawler()->selectButton('Submit')->form([
            'iu_form[makerId]' => 'ANOTHER',
            'iu_form[password]' => 'aBcDeFgH1324',
        ]);
        self::submitValid($this->client, $form);
    }

    public function testNewMakerCannotUseOtherMakersMakerId(): void
    {
        self::persistAndFlush(
            self::getArtisan(makerId: 'OTHERID'),
        );

        $this->client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($this->client);

        $form = $this->client->getCrawler()->selectButton('Submit')->form([
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
        self::submitInvalid($this->client, $form);
        self::assertSelectorTextContains('#iu_form_makerId_help + .invalid-feedback',
            'This maker ID has been already used by another maker.');

        $form = $this->client->getCrawler()->selectButton('Submit')->form([
            'iu_form[makerId]' => 'ANOTHER',
            'iu_form[password]' => 'aBcDeFgH1324',
        ]);
        self::submitValid($this->client, $form);
    }

    public function testEmailAddressNotShownForNewCreator(): void
    {
        $this->client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($this->client);
        self::assertSelectorTextNotContains('#iu_form_emailAddress_help', 'Your current email address is');
    }

    /**
     * LEGACY: grep-code-invalid-email-addresses This case is a result of past bad design decision.
     */
    public function testInvalidEmailAddressNotShownForExistingCreator(): void
    {
        self::persistAndFlush(self::getArtisan(makerId: 'CREATOR', emailAddress: 'garbage'));
        $this->client->request('GET', '/iu_form/start/CREATOR');
        self::skipRulesAndCaptcha($this->client);
        self::assertSelectorTextNotContains('#iu_form_emailAddress_help', 'Your current email address is');
    }

    public function testPreviousEmailAddressShownForExistingCreator(): void
    {
        self::persistAndFlush(self::getArtisan(makerId: 'CREATOR', emailAddress: 'valid@example.com'));
        $this->client->request('GET', '/iu_form/start/CREATOR');
        self::skipRulesAndCaptcha($this->client);
        self::assertSelectorTextContains('#iu_form_emailAddress_help', 'Your current email address is v***d@e*********m');
    }
}
