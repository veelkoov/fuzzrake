<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\StringList;
use App\Utils\StrUtils;

class Differ
{
    private Printer $printer;

    public function __construct(Printer $printer)
    {
        $this->printer = $printer;
    }

    public function showDiff(Field $field, Artisan $old, Artisan $new, ?Artisan $imported): void
    {
        $newVal = $new->get($field) ?: '';
        $oldVal = $old->get($field) ?: '';
        $impVal = $imported ? $imported->get($field) : null;

        if ($oldVal !== $newVal) {
            if ($field->isList()) {
                $this->showListDiff($field->name(), $oldVal, $newVal, $impVal);
            } else {
                $this->showSingleValueDiff($field->name(), $oldVal, $newVal, $impVal);
            }
        }
    }

    private function showListDiff(string $fieldName, $oldVal, $newVal, $impVal = null): void
    {
        $oldValItems = StringList::unpack($oldVal);
        $newValItems = StringList::unpack($newVal);

        foreach ($oldValItems as &$item) {
            if (!in_array($item, $newValItems)) {
                $item = Printer::formatDeleted($item);
            }

            $item = StrUtils::strSafeForCli($item);
        }

        foreach ($newValItems as &$item) {
            if (!in_array($item, $oldValItems)) {
                $item = Printer::formatAdded($item);
            }

            $item = StrUtils::strSafeForCli($item);
        }

        if ($impVal && $impVal !== $newVal) {
            $impVal = StrUtils::strSafeForCli($impVal ?: '');
            $this->printer->writeln("IMP $fieldName: ".Printer::formatImported($impVal));
        }

        if ($oldVal) { // In case order changed or duplicates got removed, etc.
            $this->printer->writeln("OLD $fieldName: ".implode('|', $oldValItems));
        }

        $this->printer->writeln("NEW $fieldName: ".implode('|', $newValItems));
    }

    private function showSingleValueDiff(string $fieldName, $oldVal, $newVal, $impVal = null): void
    {
        if ($impVal && $impVal !== $newVal && !$this->skipImpValue($fieldName)) {
            $impVal = StrUtils::strSafeForCli($impVal ?: '');
            $this->printer->writeln("IMP $fieldName: ".Printer::formatImported($impVal));
        }

        if ($oldVal) {
            $oldVal = StrUtils::strSafeForCli($oldVal);
            $this->printer->writeln("OLD $fieldName: ".Printer::formatDeleted($oldVal));
        }

        if ($newVal) {
            $newVal = StrUtils::strSafeForCli($newVal);
            $this->printer->writeln("NEW $fieldName: ".Printer::formatAdded($newVal));
        }
    }

    private function skipImpValue(string $fieldName): bool
    {
        return in_array($fieldName, [Fields::CONTACT_ALLOWED, Fields::CONTACT_METHOD, Fields::CONTACT_INFO_OBFUSCATED]);
    }
}
