<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\DataDefinitions\Fields;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Artisan\Utils;
use App\Utils\DataInputException;
use App\Utils\StringList;
use App\Utils\StrUtils;
use InvalidArgumentException;
use JsonException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form;

class IuSubmissionExtendedTest extends IuSubmissionAbstractTest
{
    private const VARIANT_FULL_DATA = 0;
    private const VARIANT_HALF_DATA_1 = 1;
    private const VARIANT_HALF_DATA_2 = 2;

    private const SKIP = '_SKIP_FIELD_';
    private const SET = '_SET_';
    private const CHECK = '_CHECK_';

    private const FIELDS = [
        'MAKER_ID'                  => ['MAKERI0', 'MAKERI1', 'MAKERI2'],
        'FORMER_MAKER_IDS'          => [
            self::SKIP,
            [self::SET => 'OLDMKR1', self::CHECK => 'OLDMKR1'],
            [self::SET => self::SKIP, self::CHECK => "MAKERI1\nOLDMKR1"],
        ],
        'NAME'                      => ['Maker name 0', 'Maker name old', 'Maker name new'],
        'FORMERLY'                  => ['Formerly 0', 'Formerly old', "Formerly old\nMaker name old"],
        'PASSWORD'                  => 'Password __VARIANT__',
        'CONTACT_INFO_OBFUSCATED'   => [
            [self::SET => 'E-mail: email@e.mail', self::CHECK => 'E-MAIL: e***l@e****l'],
            [self::SET => self::SKIP, self::CHECK => 'TELEGRAM: @te*****am'],
            [self::SET => 'Twitter: @twit_ter', self::CHECK => 'TWITTER: @tw****er'],
        ],
        'CONTACT_INFO_ORIGINAL'     => [
            [self::SET => self::SKIP, self::CHECK => 'E-mail: email@e.mail'],
            [self::SET => 'Telegram: @tele_gram', self::CHECK => 'Telegram: @tele_gram'],
            [self::SET => self::SKIP, self::CHECK => 'Twitter: @twit_ter'],
        ],
        'CONTACT_ADDRESS_PLAIN'     => [
            [self::SET => self::SKIP, self::CHECK => 'email@e.mail'],
            [self::SET => self::SKIP, self::CHECK => '@tele_gram'],
            [self::SET => self::SKIP, self::CHECK => '@twit_ter'],
        ],
        'CONTACT_METHOD'            => self::SKIP,
        'CONTACT_ALLOWED'           => ['CORRECTIONS', 'ANNOUNCEMENTS', 'FEEDBACK'],
        'INTRO'                     => 'Le intro __VARIANT__',
        'SINCE'                     => ['2020-10', '2020-11', '2020-12'],
        'LANGUAGES'                 => "Czech__VARIANT__ (limited)\nEnglish__VARIANT__",
        'COUNTRY'                   => 'C__VARIANT__',
        'STATE'                     => 'of mind __VARIANT__',
        'CITY'                      => 'Lisek __VARIANT__',
        'PAYMENT_PLANS'             => '30% upfront, rest in 100 Eur/mth until fully paid (__VARIANT__)',
        'PAYMENT_METHODS'           => "Cash\nBank transfer\nPalPay\nHugs___VARIANT__",
        'CURRENCIES_ACCEPTED'       => "USD\nEU__VARIANT__",
        'PRODUCTION_MODELS_COMMENT' => 'Comment about production models __VARIANT__',
        'PRODUCTION_MODELS'         => "Artistic liberty commissions\nPremades\nStandard commissions",
        'STYLES_COMMENT'            => 'Comment about styles __VARIANT__',
        'STYLES'                    => "Anime\nKemono\nKigurumi\nRealistic\nSemi Realistic\nSemi Toony\nToony",
        'OTHER_STYLES'              => 'OTHER_STYLES___VARIANT__',
        'ORDER_TYPES_COMMENT'       => 'Comment for order types __VARIANT__',
        'ORDER_TYPES'               => "Bodysuits (as parts/separate)\nFeetpaws (as parts/separate)\nFull digitigrade\nFull plantigrade\nHandpaws (as parts/separate)\nHead (as parts/separate)\nMini partial (head + handpaws + tail)\nPartial (head + handpaws + tail + feetpaws)\nTails (as parts/separate)\nThree-fourth (head + handpaws + tail + legs/pants + feetpaws)",
        'OTHER_ORDER_TYPES'         => 'OTHER_ORDER_TYPES___VARIANT__',
        'FEATURES_COMMENT'          => 'Comment about features __VARIANT__',
        'FEATURES'                  => "Adjustable eyebrows\nAdjustable/wiggle ears\nAttached handpaws and feetpaws\nAttached tail\nElectronics/animatronics\nExchangeable hairs\nExchangeable tongues\nFollow-me eyes\nIn-head fans\nIndoor feet\nLED eyes\nLED/EL lights\nMovable jaw\nOutdoor feet\nRemovable blush\nRemovable eyelids\nRemovable horns/antlers\nWashable heads",
        'OTHER_FEATURES'            => 'OTHER_FEATURES___VARIANT__',
        'SPECIES_COMMENT'           => 'SPECIES_COMMENT___VARIANT__',
        'SPECIES_DOES'              => 'SPECIES_DOES___VARIANT__',
        'SPECIES_DOESNT'            => 'SPECIES_DOESNT___VARIANT__',
        'NOTES'                     => 'NOTES___VARIANT__',
        'INACTIVE_REASON'           => ['', 'INACTIVE_REASON12', 'INACTIVE_REASON12'],
        'URL_FURSUITREVIEW'         => 'https://fursuitreview.com/value___VARIANT__.html',
        'URL_WEBSITE'               => 'https://mywebsite.com/value___VARIANT__.html',
        'URL_PRICES'                => "https://mywebsite.com/prices___VARIANT__.html\nhttps://mywebsite.com/prices-more___VARIANT__.html",
        'URL_FAQ'                   => 'https://mywebsite.com/faq___VARIANT__.html',
        'URL_FUR_AFFINITY'          => 'https://www.furaffinity.net/user/value___VARIANT__/',
        'URL_DEVIANTART'            => 'https://www.deviantart.com/value___VARIANT__.html',
        'URL_TWITTER'               => 'https://twitter.com/value___VARIANT__.html',
        'URL_FACEBOOK'              => 'https://www.facebook.com/value___VARIANT__.html/',
        'URL_TUMBLR'                => 'https://tumblr.com/value___VARIANT__.html',
        'URL_INSTAGRAM'             => 'https://www.instagram.com/value___VARIANT__.html/',
        'URL_YOUTUBE'               => 'https://youtube.com/value___VARIANT__.html',
        'URL_LINKLIST'              => 'https://linklist.com/value___VARIANT__.html',
        'URL_FURRY_AMINO'           => 'https://furryamino.com/value___VARIANT__.html',
        'URL_ETSY'                  => 'https://etsy.com/value___VARIANT__.html',
        'URL_THE_DEALERS_DEN'       => 'https://tdealrsdn.com/value___VARIANT__.html',
        'URL_OTHER_SHOP'            => 'https://othershop.com/value___VARIANT__.html',
        'URL_QUEUE'                 => 'https://queue.com/value___VARIANT__.html',
        'URL_SCRITCH'               => 'https://scritch.es/value___VARIANT__.html',
        'URL_FURTRACK'              => 'https://www.furtrack.com/value___VARIANT__.html',
        'URL_PHOTOS'                => "https://scritchphotos.com/value___VARIANT__.html\nhttps://www.furtrack.com/value___VARIANT__.html",
        'URL_MINIATURES'            => ['', 'URL_MINIATURE12', ''],
        'URL_OTHER'                 => 'https://other.com/value___VARIANT__.html',
        'URL_COMMISSIONS'           => "https://cst.com/value___VARIANT__.html\nhttps://cst2.com/value2___VARIANT__.html",
        'CS_LAST_CHECK'             => self::SKIP,
        'CS_TRACKER_ISSUE'          => self::SKIP,
        'BP_LAST_CHECK'             => self::SKIP,
        'BP_TRACKER_ISSUE'          => self::SKIP,
        'COMPLETENESS'              => self::SKIP,
        'OPEN_FOR'                  => self::SKIP,
        'CLOSED_FOR'                => self::SKIP,
    ];

    private const VALUE_NOT_SHOWN_IN_FORM = [
        'PASSWORD',
    ];

    private const FIELD_NOT_IN_FORM = [
        'FORMER_MAKER_IDS',
        'URL_MINIATURES',
        'CONTACT_INFO_ORIGINAL',
        'CONTACT_METHOD',
        'CONTACT_ADDRESS_PLAIN',
        'INACTIVE_REASON',
        'COMPLETENESS',
        'CS_LAST_CHECK',
        'CS_TRACKER_ISSUE',
        'BP_LAST_CHECK',
        'BP_TRACKER_ISSUE',
        'OPEN_FOR',
        'CLOSED_FOR',
    ];

    private const EXPANDED = [
        'PRODUCTION_MODELS',
        'FEATURES',
        'STYLES',
        'ORDER_TYPES',
    ];

    /**
     * Purpose of this test is to make sure:
     * - all fields, which values should be displayed in the I/U form, are,
     * - all fields, which values should NOT be displayed, are not,
     * - no newly added field gets overseen in the I/U form,
     * - all data submitted in the form is saved in the submission.
     *
     * Two tested artisans: an updated one, and a new one.
     *
     * @throws JsonException|DataInputException
     */
    public function testIuSubmissionAndImportFlow(): void
    {
        $client = static::createClient(); // Single client to be used throughout the whole test to avoid multiple in-memory DB

        $this->checkFieldsArrayCompleteness(); // Test self-test

        $oldArtisan1 = $this->getArtisanFor(self::VARIANT_HALF_DATA_1, self::SET);
        Utils::updateContact($oldArtisan1, $oldArtisan1->getContactInfoOriginal());

        self::getEM()->persist($oldArtisan1);
        self::getEM()->flush();

        $repo = self::getArtisanRepository();
        self::assertCount(1, $repo->findAll(), 'Single artisan in the DB before import');

        $oldArtisan1 = $this->getArtisanFor(self::VARIANT_HALF_DATA_1, self::CHECK);
        $this->processIuForm($client, $oldArtisan1->getMakerId(), $oldArtisan1, $this->getArtisanFor(self::VARIANT_HALF_DATA_2, self::SET));
        $this->processIuForm($client, '', new Artisan(), $this->getArtisanFor(self::VARIANT_FULL_DATA, self::SET));

        $output = $this->performImport(true);
        $this->validateConsoleOutput($output->fetch());
        self::getEM()->flush();
        self::assertCount(2, $repo->findAll(), 'Expected two artisans in the DB after import');

        $this->validateArtisanAfterImport($this->getArtisanFor(self::VARIANT_HALF_DATA_2, self::CHECK));
        $this->validateArtisanAfterImport($this->getArtisanFor(self::VARIANT_FULL_DATA, self::CHECK));
    }

    private function checkFieldsArrayCompleteness(): void
    {
        $fields = Fields::getAll();

        foreach ($fields as $fieldName => $field) {
            self::assertArrayHasKey($fieldName, self::FIELDS);
        }

        foreach (array_merge(array_keys(self::FIELDS), self::EXPANDED, self::FIELD_NOT_IN_FORM, self::VALUE_NOT_SHOWN_IN_FORM) as $fieldName) {
            self::assertArrayHasKey($fieldName, $fields->asArray());
        }
    }

    private function getArtisanFor(int $variant, string $purpose): Artisan
    {
        $result = new Artisan();

        foreach (self::FIELDS as $fieldName => $value) {
            if (is_array($value)) {
                $value = $value[$variant];
            }

            if (is_array($value)) {
                $value = $value[$purpose];
            }

            if (self::SKIP === $value) {
                continue;
            }

            if (in_array($fieldName, self::EXPANDED) && in_array($variant, [self::VARIANT_HALF_DATA_1, self::VARIANT_HALF_DATA_2])) {
                /* For testing purposes, select only either odd or even options available */
                $value = $this->selectOddOrEvenItems($value, self::VARIANT_HALF_DATA_1 === $variant);
            }

            $result->set(Fields::get($fieldName), str_replace('__VARIANT__', (string) $variant, $value));
        }

        return $result;
    }

    private function processIuForm(KernelBrowser $client, string $urlMakerId, Artisan $oldData, Artisan $newData): void
    {
        $client->request('GET', $this->getIuFormUrlForMakerId($urlMakerId));

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $this->verifyGeneratedIuForm($oldData, $client->getResponse()->getContent());

        $form = $client->getCrawler()->selectButton('Submit')->form();
        $this->setValuesInForm($form, $newData);
        $client->submit($form);

        self::assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        self::assertSelectorTextContains('h4', 'Your submission has been recorded');
    }

    private function getIuFormUrlForMakerId(string $urlMakerId): string
    {
        return '/iu_form/fill'.($urlMakerId ? '/'.$urlMakerId : '');
    }

    private function verifyGeneratedIuForm(Artisan $oldData, string $htmlBody): void
    {
        $htmlBodyLc = $this->removeFalsePositivesFromLowercaseHtml(mb_strtolower($htmlBody));
        /** @noinspection HtmlUnknownAttribute */
        $checked = pattern('<input type="(checkbox|radio)" [^>]*value="(?<value>[^"]+)" checked="checked"\s?/?>')
            ->match($htmlBodyLc)->groupBy('value')->all();

        foreach (Fields::getAll() as $fieldName => $field) {
            if (self::SKIP === self::FIELDS[$fieldName] || '' === ($value = $oldData->get($field))) {
                continue;
            }

            if (in_array($fieldName, self::VALUE_NOT_SHOWN_IN_FORM) || in_array($fieldName, self::FIELD_NOT_IN_FORM)) {
                self::assertEquals(0, substr_count($htmlBodyLc, mb_strtolower($value)),
                    "Field {$field->name()} value '$value' FOUND in the I/U form HTML code");
            } elseif (in_array($fieldName, self::EXPANDED)) {
                foreach (StringList::unpack(mb_strtolower($value)) as $valueLc) {
                    self::assertCount(1, $checked[$valueLc] ?? [],
                        "Field {$field->name()} value '$value' NOT present exactly once in the I/U form HTML code");
                }
            } elseif (Fields::SINCE == $fieldName) {
                [$year, $month] = explode('-', $value);

                self::assertFormValue('#iu_form_container form', 'iu_form[since][year]', $year);
                self::assertFormValue('#iu_form_container form', 'iu_form[since][month]', $month);
            } else {
                self::assertFormValue('#iu_form_container form', "iu_form[{$field->modelName()}]", $value);
            }
        }
    }

    private function removeFalsePositivesFromLowercaseHtml(string $inputLowercaseHtml): string
    {
        $result = pattern('(<label[^>]*>[^<]+</label>)')->prune($inputLowercaseHtml);

        /** @noinspection HtmlUnknownAttribute */
        $regex = '<select id="iu_form_since_day"[^>]*><option value="">day</option>(<option value="\d{1,2}"( selected="selected")?>\d{2}</option>)+</select>';

        return pattern($regex)->replace($result)->first()->with('');
    }

    private function setValuesInForm(Form $form, Artisan $data): void
    {
        foreach (Fields::getAll() as $fieldName => $field) {
            if (in_array($fieldName, self::FIELD_NOT_IN_FORM)) {
                continue;
            }

            $value = $data->get($field);
            $fields = $form["iu_form[{$field->modelName()}]"];

            if (Fields::SINCE === $fieldName) {
                $this->setValuesInSinceField($value, $fields);
            } elseif (in_array($fieldName, self::EXPANDED)) {
                $this->setValuesInExpandedField($value, $fields);
            } else {
                $fields->setValue($value);
            }
        }

        $field = $form['iu_form[photosCopyright]'][0];
        /* @var ChoiceFormField $field */
        $field->tick();
    }

    /**
     * @param FormField[] $fields
     */
    private function setValuesInSinceField(string $value, array $fields): void
    {
        [$year, $month] = explode('-', $value);

        if (!($fields['year'] instanceof ChoiceFormField) || !($fields['month'] instanceof ChoiceFormField) || !($fields['day'] instanceof ChoiceFormField)) {
            throw new InvalidArgumentException('Expected array of '.ChoiceFormField::class);
        }

        $fields['year']->select($year);
        $fields['month']->select($month);
        $fields['day']->select('1'); // grep-default-auto-since-day-01
    }

    private function setValuesInExpandedField(string $value, array $fields): void
    {
        $values = StringList::unpack($value);

        foreach ($fields as $formField) {
            if (!($formField instanceof ChoiceFormField)) {
                throw new InvalidArgumentException('Expected choice field');
            }

            if (in_array($formField->availableOptionValues()[0], $values)) {
                $formField->tick();
            } else {
                $formField->untick();
            }
        }
    }

    private function validateArtisanAfterImport(Artisan $expected): void
    {
        $actual = self::findArtisanByMakerId($expected->getMakerId());

        self::assertNotNull($actual);

        foreach (Fields::getAll() as $fieldName => $field) {
            /* @noinspection PhpStatementHasEmptyBodyInspection */
            if (self::SKIP === self::FIELDS[$fieldName]) {
                // Skip checking value
            } elseif (Fields::PASSWORD === $fieldName) {
                self::assertTrue(password_verify($expected->get($field), $actual->get($field)), 'Password differs');
            } else {
                self::assertEquals($expected->get($field), $actual->get($field), "Field $fieldName differs");
            }
        }
    }

    private function validateConsoleOutput(string $output): void
    {
        $output = str_replace("\r", "\n", $output);
        $output = pattern('^(OLD |NEW |IMP | *set )[^\n]+\n+', 'm')->prune($output);
        $output = pattern('^-+\n+', 'm')->prune($output);

        $output = pattern('\[WARNING\]\s+?[a-zA-Z0-9 /\n]+?\s+?changed\s+?their\s+?maker\s+?ID\s+?from\s+?[A-Z0-9]{7}\s+?to\s+?[A-Z0-9]{7}')
            ->prune($output);

        $header1 = StrUtils::artisanNamesSafeForCli($this->getArtisanFor(self::VARIANT_FULL_DATA, self::CHECK));
        $header2 = StrUtils::artisanNamesSafeForCli($this->getArtisanFor(self::VARIANT_HALF_DATA_2, self::CHECK));

        $output = str_replace([$header1, $header2], '', $output);

        $output = str_replace('[OK] Accepted for import', '', $output, $count);
        self::assertEquals(2, $count, 'Expected exactly two accepted imports');

        $output = trim($output);

        self::assertEmpty($output, "Unexpected output in the console: \n".$output);
    }

    private function selectOddOrEvenItems(string $itemsListPacked, bool $selectOdd): string
    {
        $values = StringList::unpack($itemsListPacked);

        $rest = $selectOdd ? 0 : 1;

        for ($i = 0, $c = count($values); $i < $c; ++$i) {
            if ($rest === $i % 2) {
                unset($values[$i]);
            }
        }

        return StringList::pack($values);
    }
}
