<?php

namespace App\Validator;

use App\Utils\PackedStringList;
use App\Utils\StringList;
use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class StrListLengthValidator extends ConstraintValidator
{
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof StrListLength) {
            throw new UnexpectedTypeException($constraint, StrListLength::class);
        }

        if (!StringList::isValid($value)) {
            throw new UnexpectedValueException($value, 'list<string>');
        }

        if (strlen(PackedStringList::pack($value)) > $constraint->max) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ max }}', (string) $constraint->max)
                ->addViolation();
        }
    }
}
