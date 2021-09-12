<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\DataDefinitions\Field;
use App\DataDefinitions\Fields;
use App\Utils\Arrays;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DataInputException;
use App\Utils\Json;
use App\Utils\StringList;
use App\Utils\StrUtils;
use App\Utils\UnbelievableRuntimeException;
use InvalidArgumentException;
use JsonException;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form;
use TRegx\CleanRegex\Exception\SubjectNotMatchedException;
use TRegx\CleanRegex\Match\Details\Detail;

class ExtendedTest extends AbstractTest
{
    private const VALUE_MUST_NOT_BE_SHOWN_IN_FORM = [ // Values which must never appear in the form
        'CONTACT_INFO_ORIGINAL',
        'CONTACT_ADDRESS_PLAIN',
        'PASSWORD',
    ];

    private const NOT_IN_TEST_DATA = [ // Fields which are not loaded from JSON, they are not impacted by import
        'COMPLETENESS',
        'CS_LAST_CHECK',
        'CS_TRACKER_ISSUE',
        'BP_LAST_CHECK',
        'BP_TRACKER_ISSUE',
        'OPEN_FOR',
        'CLOSED_FOR',
    ];

    private const NOT_IN_FORM = [ // Fields which are not in the form and may or may not be impacted by the import
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

    private const EXPANDED = [ // List fields in the form of multiple checkboxes
        'PRODUCTION_MODELS',
        'FEATURES',
        'STYLES',
        'ORDER_TYPES',
    ];

    private const BOOLEAN = [ // These fields are in the form of radios with "YES" or "NO" values
        'IS_MINOR',
        'WORKS_WITH_MINORS',
    ];

    /**
     * Purpose of this test is to make sure:
     * - all fields, which should be updatable by I/U form, are available and get updated after,
     * - all fields, which values should NEVER be displayed in the I/U form, are not,
     * - no newly added field gets overseen in the I/U form,
     * - all data submitted in the form is saved in the submission.
     *
     * Tested artisans with the following scenarios:
     * 1. Updated maker with full info, changes in all possible fields,
     * 2. New maker with full info,
     * 3. Updated maker with minimal starting info, full info after update,
     * 4. New maker with minimal info.
     *
     * @throws JsonException|DataInputException
     */
    public function testIuSubmissionAndImportFlow(): void
    {
        self::sanityCheck();

        $client = static::createClient(); // Single client to be used throughout the whole test to avoid multiple in-memory DB
        $repo = self::getArtisanRepository();

        $oldArtisan1 = self::getArtisanData('a1.1-persisted');
        $oldArtisan3 = self::getArtisanData('a3.1-persisted');

        self::persistAndFlush($oldArtisan1, $oldArtisan3);
        self::assertCount(2, $repo->findAll(), 'Two artisans in the DB before import');

        $oldData1 = self::getArtisanData('a1.1-persisted');
        $newData1 = self::getArtisanData('a1.2-send');
        $makerId1 = $oldData1->getMakerId();
        self::validateIuFormOldDataSubmitNew($client, $makerId1, $oldData1, $newData1);

        $oldData2 = new Artisan();
        $newData2 = self::getArtisanData('a2.2-send');
        $makerId2 = '';
        self::validateIuFormOldDataSubmitNew($client, $makerId2, $oldData2, $newData2);

        $oldData3 = self::getArtisanData('a3.1-persisted');
        $newData3 = self::getArtisanData('a3.2-send');
        $makerId3 = $oldData3->getLastMakerId();
        self::validateIuFormOldDataSubmitNew($client, $makerId3, $oldData3, $newData3);

        $oldData4 = new Artisan();
        $newData4 = self::getArtisanData('a4.2-send');
        $makerId4 = '';
        self::validateIuFormOldDataSubmitNew($client, $makerId4, $oldData4, $newData4);

        $expectedArtisans = [
            self::getArtisanData('a1.3-check'),
            self::getArtisanData('a2.3-check'),
            self::getArtisanData('a3.3-check'),
            self::getArtisanData('a4.3-check'),
        ];

        $output = $this->performImport(true);
        self::validateConsoleOutput($output->fetch(), $expectedArtisans);

        self::getEM()->flush();
        self::assertCount(4, $repo->findAll(), 'Expected three artisans in the DB after import');

        foreach ($expectedArtisans as $expectedArtisan) {
            self::validateArtisanAfterImport($expectedArtisan);
        }
    }

    /**
     * Make sure all the fields list in this test class are OK: no typos in names, no removed (legacy) fields, etc.
     */
    private static function sanityCheck(): void
    {
        foreach ((new ReflectionClass(__CLASS__))->getConstants() as $array) {
            if (is_array($array)) {
                foreach ($array as $value) {
                    self::assertArrayHasKey($value, Fields::getAll()->asArray());
                }
            }
        }
    }

    /**
     * @throws JsonException
     */
    private static function getArtisanData(string $variant): Artisan
    {
        $result = new Artisan();

        $data = Json::readFile(__DIR__."/ExtendedTestData/$variant.json");

        foreach (Fields::getAll() as $fieldName => $field) {
            if (in_array($fieldName, self::NOT_IN_TEST_DATA)) {
                continue;
            }

            self::assertArrayHasKey($fieldName, $data);
            $result->set(Fields::get($fieldName), $data[$fieldName]);

            unset($data[$fieldName]);
        }

        self::assertEquals([], $data);

        return $result;
    }

    private static function validateIuFormOldDataSubmitNew(KernelBrowser $client, string $urlMakerId, Artisan $oldData, Artisan $newData): void
    {
        $client->request('GET', self::getIuFormUrlForMakerId($urlMakerId));

        self::assertResponseStatusCodeSame(200);

        self::verifyGeneratedIuFormFilledWithData($oldData, $client->getResponse()->getContent());

        $form = $client->getCrawler()->selectButton('Submit')->form();
        self::setValuesInForm($form, $newData);
        $client->submit($form);

        self::assertResponseStatusCodeSame(302);
        $client->followRedirect();
        self::assertSelectorTextContains('h4', 'Your submission has been recorded');
    }

    private static function getIuFormUrlForMakerId(string $urlMakerId): string
    {
        return '/iu_form/fill'.($urlMakerId ? '/'.$urlMakerId : '');
    }

    private static function verifyGeneratedIuFormFilledWithData(Artisan $oldData, string $htmlBody): void
    {
        foreach (Fields::getAll() as $fieldName => $field) {
            if (in_array($fieldName, self::NOT_IN_FORM)) {
                self::assertFieldIsNotPresentInForm($field, $htmlBody);
                continue;
            }

            $value = $oldData->get($field);

            if (in_array($fieldName, self::VALUE_MUST_NOT_BE_SHOWN_IN_FORM)) {
                self::assertValueIsNotPresentInForm($value, $field, $htmlBody);
            } else {
                self::assertFieldIsPresentWithValue($value, $field, $htmlBody);
            }
        }
    }

    private static function assertFieldIsPresentWithValue(mixed $value, Field $field, string $htmlBody): void
    {
        if (in_array($field->name(), self::EXPANDED)) {
            self::assertExpandedFieldIsPresentWithValue($value, $field, $htmlBody);
        } elseif ('SINCE' === $field->name()) {
            self::assertSinceFieldIsPresentWithValue($value, $htmlBody);
        } elseif (in_array($field->name(), self::BOOLEAN)) {
            self::assertYesNoFieldIsPresentWithValue($value, $field, $htmlBody);
        } elseif ('CONTACT_ALLOWED' === $field->name()) {
            self::assertContactValueFieldIsPresentWithValue($value, $field, $htmlBody);
        } else {
            self::assertFormValue('#iu_form_container form', "iu_form[{$field->modelName()}]", $value);
        }
    }

    private static function assertExpandedFieldIsPresentWithValue(string $value, Field $field, string $htmlBody): void
    {
        $selected = pattern('<input[^>]*name="iu_form\['.$field->modelName().']\[]"[^>]*value="(?<value>[^"]+)"[^>]*>')
            ->match($htmlBody)->map(function (Detail $detail): array {
                return [$detail->group('value')->text(), str_contains($detail->text(), 'checked="checked"')];
            });
        $selected = Arrays::assoc($selected);

        $expected = StringList::unpack($value);

        foreach ($expected as $item) {
            self::assertArrayHasKey($item, $selected, "'$item' is not available in the form.");
            self::assertTrue($selected[$item], "'$item' is not checked.");

            unset($selected[$item]);
        }

        foreach ($selected as $item => $isChecked) {
            self::assertFalse($isChecked, "'$item' is checked.");
        }
    }

    private static function assertSinceFieldIsPresentWithValue(string $value, string $htmlBody): void
    {
        foreach (['year', 'month', 'day'] as $sfName) {
            if ('day' === $sfName) {
                continue; // grep-default-auto-since-day-01
            }

            $match = pattern('<select[^>]+name="iu_form\[since]\['.$sfName.']"[^>]*>.+?</select>', 's')->match($htmlBody);
            self::assertCount(1, $match, "since/$sfName didn't match exactly once.");

            try {
                $matchedText = $match->first();
            } catch (SubjectNotMatchedException $exception) {
                throw new UnbelievableRuntimeException($exception);
            }

            if ('' === $value) {
                self::assertStringNotContainsStringIgnoringCase('selected="selected"', $matchedText);
            } else {
                $valuePart = explode('-', $value)['year' === $sfName ? 0 : 1];
                self::assertStringContainsStringIgnoringCase("<option value=\"$valuePart\" selected=\"selected\">", $matchedText);
            }
        }
    }

    private static function assertYesNoFieldIsPresentWithValue(?bool $value, Field $field, string $htmlBody): void
    {
        if (null !== $value) {
            $value = $value ? 'YES' : 'NO';
        }

        self::assertRadioFieldIsPresentWithValue($value, ['YES', 'NO'], $field, $htmlBody);
    }

    private static function assertContactValueFieldIsPresentWithValue(string $value, Field $field, string $htmlBody): void
    {
        if ('' === $value) {
            $value = null;
        }

        $choices = ['NO', 'CORRECTIONS', 'ANNOUNCEMENTS', 'FEEDBACK'];

        self::assertRadioFieldIsPresentWithValue($value, $choices, $field, $htmlBody);
    }

    /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
    private static function assertRadioFieldIsPresentWithValue(?string $value, array $choices, Field $field, string $htmlBody)
    {
        self::assertTrue(null === $value || in_array($value, $choices), "'$value' is not one of the possible choices for {$field->name()}.");

        foreach ($choices as $choice) {
            $checked = $value === $choice ? 'checked="checked"' : '';

            $regexp = "<input[^>]+name=\"iu_form\[{$field->modelName()}]\"[^>]*value=\"$choice\"[^>]*{$checked}[^>]*>";
            self::assertTrue(pattern($regexp)->test($htmlBody), "{$field->name()} radio field was not present or (not) selected.");
        }
    }

    private static function assertValueIsNotPresentInForm(mixed $value, Field $field, string $htmlBody): void
    {
        if ($field->is('PASSWORD')) { // paranoid show off, and you missed some possibility, did you?
            $match = pattern('<input[^>]+name="iu_form\[password]"[^>]*>')->match($htmlBody);
            self::assertCount(1, $match);

            try {
                self::assertStringNotContainsStringIgnoringCase('value', $match->first());
            } catch (SubjectNotMatchedException $exception) {
                throw new UnbelievableRuntimeException($exception);
            }

            if ('' !== $value) {
                self::assertStringNotContainsStringIgnoringCase($value, $htmlBody);
            }

            foreach (password_algos() as $algorithm) { // grep-password-algorithms
                self::assertFalse(pattern("\$$algorithm\$")->test($htmlBody));
            }
        } else {
            self::assertStringNotContainsStringIgnoringCase($value, $htmlBody);
        }
    }

    private static function setValuesInForm(Form $form, Artisan $data): void
    {
        foreach (Fields::getAll() as $fieldName => $field) {
            if (in_array($fieldName, self::NOT_IN_FORM)) {
                continue;
            }

            $value = $data->get($field);
            if (is_bool($value)) {
                $value = $value ? 'YES' : 'NO';
            }

            $fields = $form["iu_form[{$field->modelName()}]"];

            if (Fields::SINCE === $fieldName) {
                self::setValuesInSinceField($value, $fields);
            } elseif (in_array($fieldName, self::EXPANDED)) {
                self::setValuesInExpandedField($value, $fields);
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
    private static function setValuesInSinceField(string $value, array $fields): void
    {
        if ('' === $value) {
            return;
        }

        [$year, $month] = explode('-', $value);

        if (!($fields['year'] instanceof ChoiceFormField) || !($fields['month'] instanceof ChoiceFormField) || !($fields['day'] instanceof ChoiceFormField)) {
            throw new InvalidArgumentException('Expected array of '.ChoiceFormField::class);
        }

        $fields['year']->select($year);
        $fields['month']->select((string) (int) $month);
        $fields['day']->select('1'); // grep-default-auto-since-day-01
    }

    private static function setValuesInExpandedField(string $value, array $fields): void
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

    private static function validateArtisanAfterImport(Artisan $expected): void
    {
        $actual = self::findArtisanByMakerId($expected->getMakerId());

        self::assertNotNull($actual);

        foreach (Fields::getAll() as $fieldName => $field) {
            if (Fields::PASSWORD === $fieldName) {
                self::assertTrue(password_verify($expected->get($field), $actual->get($field)), 'Password differs.');
            } else {
                self::assertEquals($expected->get($field), $actual->get($field), "Field $fieldName differs for {$expected->getMakerId()}.");
            }
        }
    }

    private static function validateConsoleOutput(string $output, array $expectedArtisans): void
    {
        $output = str_replace("\r", "\n", $output);
        $output = pattern('^(OLD |NEW |IMP | *set )[^\n]+\n+', 'm')->prune($output);
        $output = pattern('^-+\n+', 'm')->prune($output);

        $output = pattern('\[WARNING\]\s+?[a-zA-Z0-9 /\n]+?\s+?changed\s+?their\s+?maker\s+?ID\s+?from\s+?[A-Z0-9]{7}\s+?to\s+?[A-Z0-9]{7}')
            ->prune($output);

        $expectedHeaders = array_map(fn ($artisan) => StrUtils::artisanNamesSafeForCli($artisan), $expectedArtisans);

        $output = str_replace($expectedHeaders, '', $output);
        $output = str_replace('[OK] Accepted for import', '', $output, $count);
        $output = trim($output);

        self::assertEmpty($output, "Unexpected output in the console: \n".$output);

        self::assertEquals(4, $count, 'Unexpected number of imports performed.');
    }

    private static function assertFieldIsNotPresentInForm(Field $field, string $htmlBody): void
    {
        self::assertStringNotContainsStringIgnoringCase($field->modelName(), $htmlBody);
    }
}