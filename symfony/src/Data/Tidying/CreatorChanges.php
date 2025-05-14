<?php

declare(strict_types=1);

namespace App\Data\Tidying;

use App\Data\Definitions\Fields\Fields;
use App\Data\Definitions\Fields\FieldsList;
use App\Utils\Creator\SmartAccessDecorator as Creator;

class CreatorChanges
{
    private readonly Creator $changed;

    public function __construct(
        private readonly Creator $subject,
    ) {
        $this->changed = clone $subject;
    }

    public function getSubject(): Creator
    {
        return $this->subject;
    }

    public function getChanged(): Creator
    {
        return $this->changed;
    }

    public function apply(): void
    {
        foreach (Fields::persisted() as $field) {
            $this->subject->set($field, $this->changed->get($field));
        }
    }

    public function differs(FieldsList $fields = null): bool
    {
        foreach ($fields ?? Fields::persisted() as $field) {
            if ($this->getSubject()->get($field) !== $this->getChanged()->get($field)) {
                return true;
            }
        }

        return false;
    }
}
