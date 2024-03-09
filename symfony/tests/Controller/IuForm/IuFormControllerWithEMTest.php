<?php

declare(strict_types=1);

namespace App\Tests\Controller\IuForm;

use App\Data\Definitions\Ages;
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

        $form = $client->getCrawler()->selectButton('Continue')->form();
        self::submitInvalid($client, $form);

        self::skipData($client, true);

        $form = $client->getCrawler()->selectButton('Submit')->form();
        self::submitInvalid($client, $form);
    }

    public function testErrorMessagesForRequiredDataFields(): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Continue')->form();
        self::submitInvalid($client, $form);

        self::assertSelectorTextContains('#iu_form_name + .invalid-feedback',
            'This value should not be blank.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(1)',
            'Studio/maker\'s name - This value should not be blank.');
        self::assertSelectorTextContains('#iu_form_country + .invalid-feedback',
            'This value should not be blank.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(2)',
            'Country - This value should not be blank.');
        self::assertSelectorTextContains('#iu_form_ages + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(3)',
            'What is your age? - You must answer this question.');
        self::assertSelectorTextContains('#iu_form_nsfwWebsite + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(4)',
            'The websites linked above may contain "non-family-friendly" (or NSFW) content, such as, but not limited to: - You must answer this question.');
        self::assertSelectorTextContains('#iu_form_nsfwSocial + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(5)',
            'Is there a possibility of NSFW (or the type of content listed above) being liked/shared/posted/commented on by your social media account? - You must answer this question.');
        self::assertSelectorTextContains('#iu_form_makerId + .help-text + .invalid-feedback',
            'This value should not be blank.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(6)',
            '"Maker ID" - This value should not be blank.');

        $form = $client->getCrawler()->selectButton('Continue')->form([
            'iu_form[ages]'        => 'MINORS',
            'iu_form[nsfwWebsite]' => 'NO',
            'iu_form[nsfwSocial]'  => 'NO',
        ]);
        self::submitInvalid($client, $form);

        self::assertSelectorTextContains('#iu_form_worksWithMinors + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(3)',
            'Do you accept commissions from minors or people under 18? - You must answer this question.');

        $form = $client->getCrawler()->selectButton('Continue')->form([
            'iu_form[ages]'        => 'ADULTS',
            'iu_form[nsfwWebsite]' => 'NO',
            'iu_form[nsfwSocial]'  => 'NO',
        ]);
        self::submitInvalid($client, $form);

        self::assertSelectorTextContains('#iu_form_doesNsfw + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(3)',
            'Do you offer fursuit features intended for adult use?');

        self::skipData($client, true);
    }

    /**
     * @param array<string, string> $expectedErrors
     *
     * @dataProvider ageStuffFieldsDataProvider
     */
    public function testAgeStuffFields(string $ages, string $nsfwWebsite, string $nsfwSocial, ?string $doesNsfw, ?string $worksWithMinors, array $expectedErrors): void
    {
        $client = static::createClient();

        $client->request('GET', '/iu_form/start');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Continue')->form([
            'iu_form[name]'        => 'test-maker-555',
            'iu_form[country]'     => 'Finland',
            'iu_form[makerId]'     => 'MAKERID',
            'iu_form[ages]'        => $ages,
            'iu_form[nsfwWebsite]' => $nsfwWebsite,
            'iu_form[nsfwSocial]'  => $nsfwSocial,
        ]);

        if (null !== $doesNsfw) {
            $form->setValues(['iu_form[doesNsfw]' => $doesNsfw]);
        }

        if (null !== $worksWithMinors) {
            $form->setValues(['iu_form[worksWithMinors]' => $worksWithMinors]);
        }

        if ([] === $expectedErrors) {
            self::submitValid($client, $form);
            self::assertSelectorTextContains('h2', 'Contact');
        } else {
            self::submitInvalid($client, $form);
            self::assertSelectorTextContains('h2', 'Few instructions and tips');

            foreach ($expectedErrors as $selector => $message) {
                self::assertSelectorTextContains($selector, $message);
            }
        }
    }

    public function ageStuffFieldsDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            // AGES    NSFW   NSFW    DOES   WORKS     EXPECTED
            //         WEB.   SOCIAL  NSFW   W/MINORS  ERRORS
            ['MINORS', 'NO',  'NO',   null,  null,     [
                '#iu_form_worksWithMinors + .invalid-feedback' => 'You must answer this question.',
            ]],
            ['MINORS', 'NO',  'NO',   null,  'NO',     []],
            ['MINORS', 'NO',  'YES',  null,  null,     []],
            ['MINORS', 'YES', 'NO',   null,  null,     []],
            ['MINORS', 'YES', 'YES',  null,  null,     []],

            ['MIXED',  'NO',  'NO',   null,  null,     [
                '#iu_form_worksWithMinors + .invalid-feedback' => 'You must answer this question.',
            ]],
            ['MIXED',  'NO',  'NO',   null,  'NO',     []],
            ['MIXED',  'NO',  'YES',  null,  null,     []],
            ['MIXED',  'YES', 'NO',   null,  null,     []],
            ['MIXED',  'YES', 'YES',  null,  null,     []],

            ['ADULTS', 'NO',  'NO',   null,  null,     [
                '#iu_form_doesNsfw + .invalid-feedback'        => 'You must answer this question.',
            ]],
            ['ADULTS', 'NO',  'NO',   'NO',  null,     [
                '#iu_form_worksWithMinors + .invalid-feedback' => 'You must answer this question.',
            ]],
            ['ADULTS', 'NO',  'NO',   'NO',  'NO',     []],
            ['ADULTS', 'NO',  'NO',   'NO',  'YES',    []],
            ['ADULTS', 'NO',  'NO',   'YES', null,     []],

            ['ADULTS', 'NO',  'YES',  null,  null,     [
                '#iu_form_doesNsfw + .invalid-feedback'        => 'You must answer this question.',
            ]],
            ['ADULTS', 'NO',  'YES',  'NO',  null,     []],
            ['ADULTS', 'NO',  'YES',  'YES', null,     []],

            ['ADULTS', 'YES', 'NO',   null,  null,     [
                '#iu_form_doesNsfw + .invalid-feedback'        => 'You must answer this question.',
            ]],
            ['ADULTS', 'YES', 'NO',   'NO',  null,     []],
            ['ADULTS', 'YES', 'NO',   'YES', null,     []],

            ['ADULTS', 'YES', 'YES',  null,  null,     [
                '#iu_form_doesNsfw + .invalid-feedback'        => 'You must answer this question.',
            ]],
            ['ADULTS', 'YES', 'YES',  'NO',  null,     []],
            ['ADULTS', 'YES', 'YES',  'NO',  null,     []],
            ['ADULTS', 'YES', 'YES',  'YES', null,     []],
            ['ADULTS', 'YES', 'YES',  'YES', null,     []],
        );
    }

    /**
     * @dataProvider cannotSkipUnfinishedStepsDataProvider
     */
    public function testCannotSkipUnfinishedSteps(string $step, string $slashedMakerId): void
    {
        $client = static::createClient();
        $client->followRedirects(true);

        self::persistAndFlush(self::getArtisan(makerId: 'REDIREC'));

        $crawler = $client->request('GET', "/iu_form/$step$slashedMakerId");
        $uri = $crawler->getUri();

        self::assertNotNull($uri);
        self::assertMatchesRegularExpression("#/iu_form/start$slashedMakerId\$#", $uri);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function cannotSkipUnfinishedStepsDataProvider(): array
    {
        return [
            'New maker, pass+cont'      => ['contact_and_password', ''],
            'New maker, data'           => ['data', ''],
            'Existing maker, pass+cont' => ['contact_and_password', '/REDIREC'],
            'Existing maker, data'      => ['data', '/REDIREC'],
        ];
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
            self::getArtisan(makerId: 'MAKERID', ages: Ages::ADULTS, nsfwWebsite: false, nsfwSocial: false,
                doesNsfw: false, worksWithMinors: false),
        );

        $client->request('GET', '/iu_form/start/MAKERID');
        self::skipRulesAndCaptcha($client);

        $form = $client->getCrawler()->selectButton('Continue')->form([
            'iu_form[makerId]' => 'OTHERID',
        ]);
        self::submitInvalid($client, $form);
        self::assertSelectorTextContains('#iu_form_makerId_help + .invalid-feedback',
            'This maker ID has been already used by another maker.');

        $form = $client->getCrawler()->selectButton('Continue')->form([
            'iu_form[makerId]' => 'ANOTHER',
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

        $form = $client->getCrawler()->selectButton('Continue')->form([
            'iu_form[makerId]'         => 'OTHERID',
            'iu_form[name]'            => 'test-maker-555',
            'iu_form[country]'         => 'Finland',
            'iu_form[ages]'            => 'MINORS',
            'iu_form[nsfwWebsite]'     => 'NO',
            'iu_form[nsfwSocial]'      => 'NO',
            'iu_form[worksWithMinors]' => 'NO',
        ]);
        self::submitInvalid($client, $form);
        self::assertSelectorTextContains('#iu_form_makerId_help + .invalid-feedback',
            'This maker ID has been already used by another maker.');

        $form = $client->getCrawler()->selectButton('Continue')->form([
            'iu_form[makerId]' => 'ANOTHER',
        ]);
        self::submitValid($client, $form);
    }
}
