<?php

declare(strict_types=1);

namespace App\Data\Tidying;

use App\Data\Definitions\Fields\Fields;
use App\Data\Fixer\Fixer;
use App\Data\Validator\Validator;

class FixerDifferValidator
{
    private readonly Differ $differ;

    public function __construct(
        private readonly Fixer $fixer,
        private readonly Validator $validator,
        private readonly Printer $printer,
    ) {
        $this->differ = new Differ($this->printer);
    }

    public function perform(CreatorChanges $creator): void
    {
        foreach (Fields::persisted() as $field) {
            $this->printer->setCurrentContext($creator);

            $this->fixer->fix($creator->getChanged(), $field);

            if (!$this->validator->isValid($creator->getChanged(), $field)) {
                $creator->getChanged()->set($field, $creator->getSubject()->get($field));
            }

            $this->differ->showDiff($field, $creator->getSubject(), $creator->getChanged());
        }
    }
}
