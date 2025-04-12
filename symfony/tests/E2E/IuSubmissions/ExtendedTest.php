<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use App\Tests\TestUtils\JsonArtisanDataLoader;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Enforce;
use App\Utils\PackedStringList;
use App\Utils\TestUtils\UtcClockMock;
use App\Utils\UnbelievableRuntimeException;
use BackedEnum;
use Exception;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form;
use TRegx\CleanRegex\Exception\SubjectNotMatchedException;
use TRegx\CleanRegex\Match\Detail;
use TRegx\CleanRegex\Pattern;

/**
 * @medium
 */
class ExtendedTest extends AbstractTestWithEM
{
    use IuFormTrait;

    private const array VALUE_MUST_NOT_BE_SHOWN_IN_FORM = [ // Values which must never appear in the form
        Field::EMAIL_ADDRESS,
        Field::PASSWORD,
    ];

    private const array NOT_IN_FORM = [ // Fields which are not in the form and may or may not be impacted by the import
        Field::COMPLETENESS,
        Field::CS_LAST_CHECK,
        Field::CS_TRACKER_ISSUE,
        Field::OPEN_FOR,
        Field::CLOSED_FOR,
        Field::SAFE_DOES_NSFW,
        Field::SAFE_WORKS_WITH_MINORS,

        Field::FORMER_MAKER_IDS,
        Field::URL_MINIATURES,
        Field::INACTIVE_REASON,
        Field::DATE_ADDED,
        Field::DATE_UPDATED,
    ];

    private const array EXPANDED_CHECKBOXES = [ // List fields in the form of multiple checkboxes
        Field::PRODUCTION_MODELS,
        Field::FEATURES,
        Field::STYLES,
        Field::ORDER_TYPES,
    ];

    private const array EXPANDED_RADIOS = [ // Choice (enum) fields in the form of multiple radios
        Field::AGES,
    ];

    private const array BOOLEAN = [ // These fields are in the form of radios with "YES" or "NO" values
        Field::DOES_NSFW,
        Field::NSFW_SOCIAL,
        Field::NSFW_WEBSITE,
        Field::WORKS_WITH_MINORS,
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
     * 4. New maker with minimal info,
     * 5. Updated maker where the only identification mean is the former maker ID.
     *
     * @throws Exception
     */
    public function testIuSubmissionAndImportFlow(): void
    {
        UtcClockMock::start();

        self::sanityChecks();

        $repo = self::getArtisanRepository();
        $loader = new JsonArtisanDataLoader('extended_test');

        $initialArtisans = [
            $loader->getArtisanData('a1.1-persisted'),
            $loader->getArtisanData('a3.1-persisted'),
            $loader->getArtisanData('a5.1-persisted'),
        ];
        $initialCount = count($initialArtisans);

        $expectedArtisans = [
            $loader->getArtisanData('a1.3-check'),
            $loader->getArtisanData('a2.3-check'),
            $loader->getArtisanData('a3.3-check'),
            $loader->getArtisanData('a4.3-check'),
            $loader->getArtisanData('a5.3-check'),
        ];
        $finalCount = count($expectedArtisans);

        self::persistAndFlush(...$initialArtisans);
        self::assertCount($initialCount, $repo->findAll(), "Expected $initialCount artisans in the DB before import");

        $oldData1 = $loader->getArtisanData('a1.1-persisted');
        $newData1 = $loader->getArtisanData('a1.2-send', self::NOT_IN_FORM);
        $makerId1 = $oldData1->getMakerId();
        self::validateIuFormOldDataSubmitNew($makerId1, $oldData1, $newData1);

        $oldData2 = new Artisan();
        $newData2 = $loader->getArtisanData('a2.2-send', self::NOT_IN_FORM);
        $makerId2 = '';
        self::validateIuFormOldDataSubmitNew($makerId2, $oldData2, $newData2);

        $oldData3 = $loader->getArtisanData('a3.1-persisted');
        $newData3 = $loader->getArtisanData('a3.2-send', self::NOT_IN_FORM);
        $makerId3 = $oldData3->getLastMakerId();
        self::validateIuFormOldDataSubmitNew($makerId3, $oldData3, $newData3);

        $oldData4 = new Artisan();
        $newData4 = $loader->getArtisanData('a4.2-send', self::NOT_IN_FORM);
        $makerId4 = '';
        self::validateIuFormOldDataSubmitNew($makerId4, $oldData4, $newData4);

        $oldData5 = $loader->getArtisanData('a5.1-persisted');
        $newData5 = $loader->getArtisanData('a5.2-send', self::NOT_IN_FORM);
        $makerId5 = $oldData5->getLastMakerId();
        self::validateIuFormOldDataSubmitNew($makerId5, $oldData5, $newData5);

        $this->performImport($this->client, true, $finalCount);

        self::flush();
        self::assertCount($finalCount, $repo->findAll(), "Expected $finalCount artisans in the DB after import");

        foreach ($expectedArtisans as $expectedArtisan) {
            self::validateArtisanAfterImport($expectedArtisan);
        }
    }

    private static function sanityChecks(): void
    {
        foreach (self::EXPANDED_CHECKBOXES as $field) {
            self::assertNotContains($field, self::EXPANDED_RADIOS);
        }

        foreach (self::EXPANDED_RADIOS as $field) {
            self::assertNotContains($field, self::EXPANDED_CHECKBOXES);
        }
    }

    private function validateIuFormOldDataSubmitNew(string $urlMakerId, Artisan $oldData, Artisan $newData): void
    {
        $this->client->request('GET', self::getIuFormUrlForMakerId($urlMakerId));
        self::assertResponseStatusCodeIs($this->client, 200);
        self::skipRulesAndCaptcha($this->client);

        self::assertNotFalse($this->client->getResponse()->getContent());
        self::verifyGeneratedIuFormFilledWithData($oldData, $this->client->getResponse()->getContent());

        $form = $this->client->getCrawler()->selectButton('Submit')->form();
        self::setValuesInForm($form, $newData);
        self::submitValid($this->client, $form);

        self::assertIuSubmittedAnyResult($this->client);
    }

    private static function getIuFormUrlForMakerId(string $urlMakerId): string
    {
        return '/iu_form/start'.($urlMakerId ? '/'.$urlMakerId : '');
    }

    private static function verifyGeneratedIuFormFilledWithData(Artisan $oldData, string $htmlBody): void
    {
        self::assertStringContainsStringIgnoringCase(self::fieldToFormFieldName(Field::NAME), $htmlBody,
            'Sanity check - checking field presence on page - failed.');

        foreach (Fields::all() as $field) {
            if (in_array($field, self::NOT_IN_FORM)) {
                self::assertStringNotContainsStringIgnoringCase(self::fieldToFormFieldName($field), $htmlBody,
                    "$field->value should not be present on the page.");
                self::assertFalse($field->isInIuForm());
                continue;
            }

            self::assertTrue($field->isInIuForm());
            $value = $oldData->get($field);

            if (in_array($field, self::VALUE_MUST_NOT_BE_SHOWN_IN_FORM)) {
                self::assertValueIsNotPresentInForm(Enforce::string($value), $field, $htmlBody);
            } else {
                self::assertFieldIsPresentWithValue($value, $field, $htmlBody);
            }
        }
    }

    private static function assertFieldIsPresentWithValue(mixed $value, Field $field, string $htmlBody): void
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (in_array($field, self::EXPANDED_CHECKBOXES) || in_array($field, self::EXPANDED_RADIOS)) {
            self::assertExpandedFieldIsPresentWithValue($value, $field, $htmlBody);
        } elseif (Field::SINCE === $field) {
            self::assertSinceFieldIsPresentWithValue(Enforce::string($value), $htmlBody);
        } elseif (in_array($field, self::BOOLEAN)) {
            self::assertYesNoFieldIsPresentWithValue(Enforce::nBool($value), $field, $htmlBody);
        } elseif (Field::CONTACT_ALLOWED === $field) {
            self::assertContactValueFieldIsPresentWithValue(Enforce::nString($value), $field, $htmlBody);
        } else {
            if ($field->isList()) {
                $value = PackedStringList::pack(Enforce::strList($value));
            }

            self::assertIsString($value, "Unexpected $field->value value type");
            self::assertFormValue('form[name=iu_form]', "iu_form[{$field->modelName()}]", $value, "Field $field->name is not present with the value '$value'");
        }
    }

    private static function assertExpandedFieldIsPresentWithValue(mixed $value, Field $field, string $htmlBody): void
    {
        if (null === $value || '' === $value) {
            $value = [];
        } elseif (!is_array($value)) {
            $value = [$value];
        }

        $items = Enforce::strList($value);

        $optionalArraySuffix = in_array($field, self::EXPANDED_CHECKBOXES) ? '[]' : '';
        $selected = Pattern::inject('<input[^>]*name="iu_form\[@]@"[^>]*value="(?<value>[^"]+)"[^>]*>', [$field->modelName(), $optionalArraySuffix])
            ->match($htmlBody)->toMap(fn (Detail $detail): array => [$detail->get('value') => str_contains($detail->text(), 'checked="checked"')]);

        foreach ($items as $item) {
            self::assertArrayHasKey($item, $selected, "'$item' is not an option for '$field->value'.");
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

            $match = pattern('<select[^>]+name="iu_form\[since]\['.$sfName.']"[^>]*>.+?</select>', 's')->search($htmlBody);
            self::assertCount(1, $match, "since/$sfName didn't match exactly once.");

            try {
                $matchedText = $match->first();
            } catch (SubjectNotMatchedException $exception) {
                throw new UnbelievableRuntimeException($exception);
            }

            if ('' === $value) {
                self::assertStringNotContainsStringIgnoringCase('selected="selected"', $matchedText);
            } else {
                $valuePart = ltrim(explode('-', $value)['year' === $sfName ? 0 : 1], '0');
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

    private static function assertContactValueFieldIsPresentWithValue(?string $value, Field $field, string $htmlBody): void
    {
        $choices = ['NO', 'CORRECTIONS', 'ANNOUNCEMENTS', 'FEEDBACK'];

        self::assertRadioFieldIsPresentWithValue($value, $choices, $field, $htmlBody);
    }

    /**
     * @param string[] $choices
     */
    private static function assertRadioFieldIsPresentWithValue(?string $value, array $choices, Field $field, string $htmlBody): void
    {
        self::assertTrue(null === $value || in_array($value, $choices), "'$value' is not one of the possible choices for $field->value.");

        foreach ($choices as $choice) {
            $checked = $value === $choice ? 'checked="checked"' : '';

            $regexp = "<input[^>]+name=\"iu_form\[{$field->modelName()}]\"[^>]*value=\"$choice\"[^>]*{$checked}[^>]*>";
            self::assertTrue(pattern($regexp)->test($htmlBody), "$field->value radio field was not present or (not) selected.");
        }
    }

    private static function assertValueIsNotPresentInForm(string $value, Field $field, string $htmlBody): void
    {
        if (Field::PASSWORD === $field) { // paranoid show off, and you missed some possibility, did you?
            $match = pattern('<input[^>]+name="iu_form\[password]"[^>]*>')->search($htmlBody);
            self::assertCount(1, $match);

            try {
                $textMatch = $match->first();

                self::assertStringNotContainsStringIgnoringCase('value', $textMatch); // Needle = attribute name
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
            if ('' !== $value) {
                self::assertStringNotContainsStringIgnoringCase($value, $htmlBody);
            }
        }
    }

    private static function setValuesInForm(Form $form, Artisan $data): void
    {
        foreach (Fields::all() as $field) {
            if (in_array($field, self::NOT_IN_FORM)) {
                continue;
            }

            $value = $data->get($field);
            if (is_bool($value)) {
                $value = $value ? 'YES' : 'NO';
            } elseif ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            $fields = $form["iu_form[{$field->modelName()}]"];

            if (Field::SINCE === $field) {
                $fields = Enforce::arrayOf($fields, FormField::class);

                self::setValuesInSinceField(Enforce::string($value), $fields);
            } elseif (in_array($field, self::EXPANDED_CHECKBOXES)) {
                $fields = Enforce::arrayOf($fields, FormField::class);

                self::setValuesInExpandedField(Enforce::strList($value), $fields);
            } else {
                $formField = Enforce::objectOf($fields, FormField::class);

                if ($field->isList()) {
                    $value = PackedStringList::pack(Enforce::strList($value));
                }

                $formField->setValue(Enforce::string($value));
            }
        }

        // Select them both always "just in case"
        self::selectCheckbox($form['iu_form[changePassword]']);
        self::selectCheckbox($form['iu_form[verificationAcknowledgement]']);
        self::selectInChoiceFormField($form['iu_form[photosCopyright]'], 0);
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

    /**
     * @param list<string> $value
     * @param FormField[]  $fields
     */
    private static function setValuesInExpandedField(array $value, array $fields): void
    {
        foreach ($fields as $formField) {
            if (!($formField instanceof ChoiceFormField)) {
                throw new InvalidArgumentException('Expected choice field');
            }

            if (in_array($formField->availableOptionValues()[0], $value)) {
                $formField->tick();
            } else {
                $formField->untick();
            }
        }
    }

    private static function validateArtisanAfterImport(Artisan $expected): void
    {
        $actual = self::findArtisanByMakerId($expected->getMakerId());

        foreach (Fields::all() as $fieldName => $field) {
            if (Field::PASSWORD === $field) {
                self::assertTrue(password_verify($expected->getString($field), $actual->getString($field)), 'Password differs.');
            } elseif ($field->isList()) {
                self::assertEqualsCanonicalizing($expected->getStringList($field), $actual->getStringList($field), "Field $fieldName differs for {$expected->getMakerId()}.");
            } else {
                self::assertEquals($expected->get($field), $actual->get($field), "Field $fieldName differs for {$expected->getMakerId()}.");
            }
        }
    }

    private static function fieldToFormFieldName(Field $field): string
    {
        return "iu_form[{$field->modelName()}]";
    }

    /**
     * @param FormField|array<mixed> $checkbox
     */
    public static function selectCheckbox(FormField|array $checkbox): void
    {
        self::assertInstanceOf(FormField::class, $checkbox);

        $checkbox->setValue('1');
    }

    /**
     * @param FormField|array<mixed> $choiceFields
     */
    public static function selectInChoiceFormField(FormField|array $choiceFields, int $choiceIdx): void
    {
        self::assertTrue(is_array($choiceFields) && $choiceFields[$choiceIdx] instanceof ChoiceFormField);

        $choiceFields[$choiceIdx]->tick();
    }
}
