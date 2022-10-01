<?php

declare(strict_types=1);

namespace App\Utils\DataTidying;

use App\DataDefinitions\Fields\Fields;
use App\DataDefinitions\Fields\FieldsList;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class ArtisanChanges
{
    private readonly Artisan $changed;

    public function __construct(
        private readonly Artisan $subject,
    ) {
        $this->changed = clone $subject;
    }

    public function getSubject(): Artisan
    {
        return $this->subject;
    }

    public function getChanged(): Artisan
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
