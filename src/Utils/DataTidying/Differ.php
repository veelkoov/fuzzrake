<?php

declare(strict_types=1);

namespace App\Utils\DataTidying;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\SecureValues;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;
use App\Utils\StrUtils;

class Differ
{
    public function __construct(
        private readonly Printer $printer,
    ) {
    }

    public function showDiff(Field $field, Artisan $old, Artisan $new): void
    {
        $newVal = StrUtils::asStr($new->get($field) ?? '');
        $oldVal = StrUtils::asStr($old->get($field) ?? '');

        if ($oldVal === $newVal || SecureValues::hideOnAdminScreen($field)) {
            return;
        }

        if ($field->isList()) {
            $this->showListDiff($field->name, $oldVal, $newVal);
        } else {
            $this->showSingleValueDiff($field, $oldVal, $newVal);
        }
    }

    private function showListDiff(string $fieldName, string $oldVal, string $newVal): void
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

        if ($oldVal) { // In case order changed or duplicates got removed, etc.
            $this->printer->writeln("OLD $fieldName $q".implode($n, $oldValItems).$q);
        }

        $this->printer->writeln("NEW $fieldName $q".implode($n, $newValItems).$q);
    }

    private function showSingleValueDiff(Field $field, string $oldVal, string $newVal): void
    {
        $q = Formatter::shy('"');

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
