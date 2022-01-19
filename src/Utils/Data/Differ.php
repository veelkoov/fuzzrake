<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\FieldsList;
use App\DataDefinitions\Fields\SecureValues;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Console\Formatter;
use App\Utils\StringList;
use App\Utils\StrUtils;

class Differ
{
    private readonly FieldsList $skipImpValue;

    public function __construct(
        private readonly Printer $printer,
    ) {
        $this->skipImpValue = new FieldsList([
            Field::CONTACT_ALLOWED,
            Field::CONTACT_METHOD,
            Field::CONTACT_INFO_OBFUSCATED,
        ]);
    }

    public function showDiff(Field $field, Artisan $old, Artisan $new, ?Artisan $imported): void
    {
        $newVal = StrUtils::asStr($new->get($field) ?? '');
        $oldVal = StrUtils::asStr($old->get($field) ?? '');
        $impVal = StrUtils::asStr($imported?->get($field) ?? '');

        if ($oldVal === $newVal || SecureValues::hideImportDiff($field)) {
            return;
        }

        if ($field->isList()) {
            $this->showListDiff($field->name, $oldVal, $newVal, $impVal);
        } else {
            $this->showSingleValueDiff($field, $oldVal, $newVal, $impVal);
        }
    }

    private function showListDiff(string $fieldName, $oldVal, $newVal, $impVal = null): void
    {
        $oldValItems = StringList::unpack($oldVal);
        $newValItems = StringList::unpack($newVal);

        foreach ($oldValItems as &$item) {
            if (!in_array($item, $newValItems)) {
                $item = Formatter::deleted($item);
            }

            $item = StrUtils::strSafeForCli($item);
        }

        foreach ($newValItems as &$item) {
            if (!in_array($item, $oldValItems)) {
                $item = Formatter::added($item);
            }

            $item = StrUtils::strSafeForCli($item);
        }

        $q = Formatter::shy('"');
        $n = Formatter::shy('\n');

        if ($impVal && $impVal !== $newVal) {
            $impVal = StrUtils::strSafeForCli($impVal);
            $this->printer->writeln("IMP $fieldName $q".Formatter::imported($impVal).$q);
        }

        if ($oldVal) { // In case order changed or duplicates got removed, etc.
            $this->printer->writeln("OLD $fieldName $q".implode($n, $oldValItems).$q);
        }

        $this->printer->writeln("NEW $fieldName $q".implode($n, $newValItems).$q);
    }

    private function showSingleValueDiff(Field $field, $oldVal, $newVal, $impVal = null): void
    {
        $q = Formatter::shy('"');

        if ($impVal && $impVal !== $newVal && !$this->skipImpValue->has($field)) {
            $impVal = StrUtils::strSafeForCli($impVal);
            $this->printer->writeln("IMP $field->name $q".Formatter::imported($impVal).$q);
        }

        if ($oldVal) {
            $oldVal = StrUtils::strSafeForCli($oldVal);
            $this->printer->writeln("OLD $field->name $q".Formatter::deleted($oldVal).$q);
        }

        if ($newVal) {
            $newVal = StrUtils::strSafeForCli($newVal);
            $this->printer->writeln("NEW $field->name $q".Formatter::added($newVal).$q);
        }
    }
}
