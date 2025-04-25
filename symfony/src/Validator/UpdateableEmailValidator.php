<?php

declare(strict_types=1);

namespace App\Validator;

use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Email;
use App\Utils\Enforce;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Override;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UpdateableEmailValidator extends ConstraintValidator
{
    private const string ERROR_MESSAGE = 'A valid email address is required.'
        .' This field should not contain anything else, just the.email@address.'
        .' If you do not agree to provide your email, disallow any contact.';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!($value instanceof Creator)) {
            throw new InvalidArgumentException(self::class.' supports only '.Creator::class.' instances');
        }

        if (ContactPermit::NO === $value->getContactAllowed()) {
            return; // No contact permit. Ignore any leftover value. Do not clear - not the responsibility of this class.
        }

        if ('' === $value->getEmailAddress() && Email::isValid($this->getOldEmailAddressOrEmpty($value))) {
            return; // No new email given, but an old valid one is present
        }

        if (Email::isValid($value->getEmailAddress())) {
            return; // New provided email address is OK
        }

        $this->context->buildViolation(self::ERROR_MESSAGE)
            ->atPath(Field::EMAIL_ADDRESS->modelName())
            ->addViolation();
    }

    private function getOldEmailAddressOrEmpty(Creator $creator): string
    {
        $privateDataE = $creator->getCreator()->getPrivateData() ?? new stdClass();

        return Enforce::string($this->entityManager->getUnitOfWork()
            ->getOriginalEntityData($privateDataE)[Field::EMAIL_ADDRESS->modelName()] ?? '');
    }
}
