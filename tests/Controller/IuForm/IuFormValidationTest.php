<?php

declare(strict_types=1);

namespace App\Tests\Controller\IuForm;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class IuFormValidationTest extends FuzzrakeWebTestCase
{
    use IuFormTrait;

    public function testErrorMessagesForRequiredDataFields(): void
    {
        self::haveACreatorUser();
        self::loginCreatorUser();

        self::$client->request('GET', '/user/iu_form/start');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form();
        self::submitInvalid($form);

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
        self::assertSelectorTextContains('#iu_form_creatorId + .help-text + .invalid-feedback',
            'This value should not be blank.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(6)',
            '"Maker ID" - This value should not be blank.');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[ages]'        => 'MINORS',
            'iu_form[nsfwWebsite]' => 'NO',
            'iu_form[nsfwSocial]'  => 'NO',
        ]);
        self::submitInvalid($form);

        self::assertSelectorTextContains('#iu_form_worksWithMinors + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(3)',
            'Do you accept commissions from minors or people under 18? - You must answer this question.');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[ages]'        => 'ADULTS',
            'iu_form[nsfwWebsite]' => 'NO',
            'iu_form[nsfwSocial]'  => 'NO',
        ]);
        self::submitInvalid($form);

        self::assertSelectorTextContains('#iu_form_doesNsfw + .invalid-feedback',
            'You must answer this question.');
        self::assertSelectorTextContains('#form_errors_top li:nth-child(3)',
            'Do you offer fursuit features intended for adult use?');
    }

    /**
     * @param array<string, string> $expectedErrors
     */
    #[DataProvider('ageStuffFieldsDataProvider')]
    public function testAgeStuffFields(string $ages, string $nsfwWebsite, string $nsfwSocial, ?string $doesNsfw, ?string $worksWithMinors, array $expectedErrors): void
    {
        self::haveACreatorUser();
        self::loginCreatorUser();

        self::$client->request('GET', '/user/iu_form/start');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]' => 'test-maker-555',
            'iu_form[country]' => 'Finland',
            'iu_form[creatorId]' => 'TEST001',
            'iu_form[ages]' => $ages,
            'iu_form[nsfwWebsite]' => $nsfwWebsite,
            'iu_form[nsfwSocial]' => $nsfwSocial,
        ]);

        if (null !== $doesNsfw) {
            $form->setValues(['iu_form[doesNsfw]' => $doesNsfw]);
        }

        if (null !== $worksWithMinors) {
            $form->setValues(['iu_form[worksWithMinors]' => $worksWithMinors]);
        }

        if ([] === $expectedErrors) {
            self::submitValid($form);
        } else {
            self::submitInvalid($form);

            foreach ($expectedErrors as $selector => $message) {
                self::assertSelectorTextContains($selector, $message);
            }
        }
    }

    /**
     * @return list<array{string, string, string, ?string, ?string, array<string, string>}>
     */
    public static function ageStuffFieldsDataProvider(): array
    {
        return [
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
        ];
    }
}
