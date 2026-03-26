<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Entity\User;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use App\Tests\TestUtils\Paths;
use App\Tests\TestUtils\UserCreator;
use App\Tests\TestUtils\YamlCreatorsDataLoader;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Enforce;
use App\Utils\PackedStringList;
use App\Utils\Regexp\Pattern;
use BackedEnum;
use Composer\Pcre\Preg;
use Composer\Pcre\Regex;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form;

#[Medium]
class ExtendedTest extends IuSubmissionsTestCase
{
    use IuFormTrait;
    use ClockSensitiveTrait;

    private const array VALUE_MUST_NOT_BE_SHOWN_IN_FORM = [// Values which must never appear in the form
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

    private const array BOOLEAN_REQUIRED = [ // These fields are in the form of radios with "YES" or "NO" values
        Field::DOES_NSFW,
        Field::NSFW_SOCIAL,
        Field::NSFW_WEBSITE,
        Field::WORKS_WITH_MINORS,
    ];

    private const array BOOLEAN_OPTIONAL = [ // These fields are in the form of radios with "YES" or "NO" or "Not specified" values
        Field::HAS_ALLERGY_WARNING,
        Field::OFFERS_PAYMENT_PLANS,
    ];

    /**
     * Purpose of this test is to make sure:
     * - all fields, which should be updatable by I/U form, are available and get updated after,
     * - all fields, which values should NEVER be displayed in the I/U form, are not,
     * - no newly added field gets overseen in the I/U form,
     * - all data submitted in the form is saved in the submission.
     *
     * Tested creators with the following scenarios:
     * 1. Updated creator with full info, changes in all possible fields,
     * 2. New creator with full info,
     * 3. Updated creator with minimal starting info, full info after update,
     * 4. New creator with minimal info,
     * 5. Updated creator where the only identification mean is the former creator ID.
     *
     * @throws Exception
     */
    public function testIuSubmissionAndImportFlow(): void
    {
        self::mockTime();

        self::sanityChecks();

        $loader = new YamlCreatorsDataLoader(Paths::getTestDataPath('extended_test.yaml'));

        self::persistAndFlush(...$loader->before->getValuesArray(), ...$loader->users->getValuesArray());
        self::assertCount($loader->before->count(), self::getCreatorRepository()->findAll(),
            "Expected {$loader->before->count()} creators in the DB before import.");

        foreach ($loader->aliases as $label) {
            $user = $loader->users->get($label);
            $oldData = $loader->before->hasKey($label) ? $loader->before->get($label) : UserCreator::get();
            $newData = $loader->update->get($label);

            self::validateIuFormOldDataSubmitNew($user, $oldData, $newData);
        }

        $this->performImports($loader->after->count());

        self::flush();
        self::assertCount($loader->after->count(), self::getCreatorRepository()->findAll(),
            "Expected {$loader->after->count()} creators in the DB after import.");

        foreach ($loader->after as $expectedCreator) {
            self::validateCreatorAfterImport($expectedCreator);
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

    private function validateIuFormOldDataSubmitNew(User $user, Creator $oldData, Creator $newData): void
    {
        self::loginUser($user);
        self::$client->request('GET', '/user/iu_form/start');
        self::assertResponseStatusCodeIs(200);
        self::skipRules();

        self::assertNotFalse(self::$client->getResponse()->getContent());
        self::verifyGeneratedIuFormFilledWithData($oldData, self::$client->getResponse()->getContent());

        $form = self::$client->getCrawler()->selectButton('Submit')->form();
        $this->setValuesInForm($form, $newData);
        self::submitValid($form);

        self::assertIuSubmissionQueued();
    }

    private static function verifyGeneratedIuFormFilledWithData(Creator $oldData, string $htmlBody): void
    {
        self::assertStringContainsStringIgnoringCase(self::fieldToFormFieldName(Field::NAME), $htmlBody,
            'Sanity check - checking field presence on page - failed.');

        foreach (Fields::all() as $field) {
            if (!$field->isInIuForm()) {
                self::assertStringNotContainsStringIgnoringCase(self::fieldToFormFieldName($field), $htmlBody,
                    "$field->value should not be present on the page.");
                self::assertFalse($field->isInIuForm());
                continue;
            }

            self::assertTrue($field->isInIuForm());
            $value = $oldData->get($field);

            if (arr_contains(self::VALUE_MUST_NOT_BE_SHOWN_IN_FORM, $field)) {
                self::assertValueIsNotPresentInForm(Enforce::string($value), $htmlBody);
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

        if (arr_contains(self::EXPANDED_CHECKBOXES, $field) || arr_contains(self::EXPANDED_RADIOS, $field)) {
            self::assertExpandedFieldIsPresentWithValue($value, $field, $htmlBody);
        } elseif (Field::SINCE === $field) {
            self::assertSinceFieldIsPresentWithValue(Enforce::string($value), $htmlBody);
        } elseif (arr_contains(self::BOOLEAN_REQUIRED, $field)) {
            self::assertYesNoFieldIsPresentWithValue(Enforce::nBool($value), $field, false, $htmlBody);
        } elseif (arr_contains(self::BOOLEAN_OPTIONAL, $field)) {
            self::assertYesNoFieldIsPresentWithValue(Enforce::nBool($value), $field, true, $htmlBody);
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

        $allMatches = Pattern::fromTemplate('<input[^>]*name="iu_form\[@]@"[^>]*value="(?<value>[^"]+)"[^>]*>', [
            $field->modelName(),
            arr_contains(self::EXPANDED_CHECKBOXES, $field) ? '[]' : '',
        ])->strictMatchAll($htmlBody)->matches;

        $selected = [];
        foreach ($allMatches[0] as $index => $wholeMatch) {
            $selected[$allMatches['value'][$index]] = str_contains($wholeMatch, 'checked="checked"');
        }

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

            $match = Regex::matchAllStrictGroups('~<select[^>]+name="iu_form\[since]\['.$sfName.']"[^>]*>.+?</select>~s', $htmlBody);
            self::assertSame(1, $match->count, "since/$sfName didn't match exactly once.");
            $matchedText = $match->matches[0][0];

            if ('' === $value) {
                self::assertStringNotContainsStringIgnoringCase('selected="selected"', $matchedText);
            } else {
                $valuePart = ltrim(explode('-', $value)['year' === $sfName ? 0 : 1], '0');
                self::assertStringContainsStringIgnoringCase("<option value=\"$valuePart\" selected=\"selected\">", $matchedText);
            }
        }
    }

    private static function assertYesNoFieldIsPresentWithValue(?bool $value, Field $field, bool $optional, string $htmlBody): void
    {
        $value = match ($value) {
            true => 'YES',
            false => 'NO',
            null => $optional ? '' : $value,
        };

        $choices = $optional ? ['YES', 'NO', ''] : ['YES', 'NO'];
        self::assertRadioFieldIsPresentWithValue($value, $choices, $field, $htmlBody);
    }

    /**
     * @param string[] $choices
     */
    private static function assertRadioFieldIsPresentWithValue(?string $value, array $choices, Field $field, string $htmlBody): void
    {
        self::assertTrue(null === $value || arr_contains($choices, $value), "'$value' is not one of the possible choices for $field->value.");

        foreach ($choices as $choice) {
            $checked = $value === $choice ? 'checked="checked"' : '';

            $pattern = "~<input[^>]+name=\"iu_form\[{$field->modelName()}]\"[^>]*value=\"$choice\"[^>]*{$checked}[^>]*>~";
            self::assertTrue(Preg::isMatch($pattern, $htmlBody), "$field->value radio field was not present or (not) selected.");
        }
    }

    private static function assertValueIsNotPresentInForm(string $value, string $htmlBody): void
    {
        if ('' !== $value) {
            self::assertStringNotContainsStringIgnoringCase($value, $htmlBody);
        }
    }

    private function setValuesInForm(Form $form, Creator $data): void
    {
        foreach (Fields::inIuForm() as $field) {
            $value = $data->get($field);

            if (is_bool($value)) {
                $value = $value ? 'YES' : 'NO';
            } elseif (arr_contains(self::BOOLEAN_OPTIONAL, $field) && null === $value) {
                $value = '';
            } elseif ($value instanceof BackedEnum) {
                $value = $value->value;
            }

            $fields = $form["iu_form[{$field->modelName()}]"];

            if (Field::SINCE === $field) {
                $fields = Enforce::arrayOf($fields, FormField::class);

                self::setValuesInSinceField(Enforce::string($value), $fields);
            } elseif (arr_contains(self::EXPANDED_CHECKBOXES, $field)) {
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
            if (!$formField instanceof ChoiceFormField) {
                throw new InvalidArgumentException('Expected choice field');
            }

            /* @phpstan-ignore method.internal (Don't know how to do that nicely) */
            if (arr_contains($value, $formField->availableOptionValues()[0])) {
                $formField->tick();
            } else {
                $formField->untick();
            }
        }
    }

    private static function validateCreatorAfterImport(Creator $expected): void
    {
        $actual = self::findCreatorByCreatorId($expected->getCreatorId());

        foreach (Fields::all() as $fieldName => $field) {
            if ($field->isList()) {
                self::assertEqualsCanonicalizing($expected->getStringList($field), $actual->getStringList($field), "Field $fieldName differs for {$expected->getCreatorId()}.");
            } else {
                self::assertEquals($expected->get($field), $actual->get($field), "Field $fieldName differs for {$expected->getCreatorId()}.");
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
